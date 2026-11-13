<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\DataLoader\DataLoader;
use BEAR\Resource\Exception\UriException;
use Override;
use ReflectionMethod;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_pop;
use function assert;
use function count;
use function is_array;
use function is_numeric;
use function parse_url;
use function sprintf;
use function str_contains;
use function ucfirst;
use function uri_template;

use const PHP_URL_FRAGMENT;

/**
 * Sequential link crawler
 *
 * @psalm-import-type Body from Types
 * @psalm-import-type BodyList from Types
 * @psalm-import-type BodyOrStringList from Types
 * @psalm-import-type Query from Types
 * @psalm-import-type QueryList from Types
 */
final class LinkCrawler implements LinkCrawlerInterface
{
    /** @var array<string, array<mixed>|null> */
    private array $cache = [];

    public function __construct(
        private readonly InvokerInterface $invoker,
        private readonly FactoryInterface $factory,
        private readonly DataLoader|null $dataLoader = null,
    ) {
    }

    #[Override]
    public function crawl(array $annotations, LinkType $link, array &$bodyList): void
    {
        // A crawl follows each href to the resource it names; links in the fragment are not followed
        foreach ($annotations as $annotation) {
            if ($annotation->crawl === $link->key && str_contains((string) parse_url($annotation->href, PHP_URL_FRAGMENT), '=')) {
                throw new UriException(sprintf('%s: a link in the fragment of a #[Link] href is not followed', $annotation->href), 500);
            }
        }

        // Process DataLoader-enabled links first
        /** @var list<array<string, mixed>> $bodyList */
        $this->dataLoader?->load($annotations, $link, $bodyList);

        // Process non-DataLoader links
        foreach ($bodyList as &$body) {
            $this->crawlBody($annotations, $link, $body);
        }

        unset($body);
    }

    /**
     * @param list<Link>           $annotations
     * @param array<string, mixed> $body
     *
     * @param-out array<string, mixed> $body
     */
    private function crawlBody(array $annotations, LinkType $link, array &$body): void
    {
        foreach ($annotations as $annotation) {
            if ($annotation->crawl !== $link->key) {
                continue;
            }

            // Skip DataLoader-enabled links (already processed by DataLoader)
            if ($annotation->dataLoader !== null && $this->dataLoader !== null) {
                continue;
            }

            $uri = uri_template($annotation->href, $body);
            $rel = $this->factory->newInstance($uri);
            $query = (new Uri($uri))->query;
            $request = new Request($this->invoker, $rel, Method::GET, $query);
            $hash = $request->hash();

            if (array_key_exists($hash, $this->cache)) {
                $body[$annotation->rel] = $this->cache[$hash];

                continue;
            }

            // Execute request and get result
            $ro = $this->invoker->invoke($request);
            $result = $ro->body;
            assert(is_array($result) || $result === null);
            $this->cache[$hash] = $result;
            $body[$annotation->rel] = $result;

            // Process nested crawl recursively (even for empty arrays to trigger DataLoader)
            if (! is_array($result)) {
                continue;
            }

            $this->processNestedCrawl($ro, $request->method->value, $link, $annotation->rel, $body);
        }
    }

    /**
     * Process nested crawl for child resources
     *
     * @param array<string, mixed> $body
     *
     * @param-out array<string, mixed> $body
     */
    private function processNestedCrawl(
        ResourceObject $ro,
        string $method,
        LinkType $link,
        string $rel,
        array &$body,
    ): void {
        $nestedAnnotations = $this->getLinkAnnotations($ro, $method);

        // Check if there are any crawl annotations for this link
        $hasCrawlAnnotation = false;
        foreach ($nestedAnnotations as $annotation) {
            if ($annotation->crawl === $link->key) {
                $hasCrawlAnnotation = true;
                break;
            }
        }

        if (! $hasCrawlAnnotation) {
            return;
        }

        /** @var array<mixed>|null $result */
        $result = $body[$rel];
        assert(is_array($result));

        // Handle empty arrays - still call crawl() to trigger DataLoader
        if ($result === []) {
            /** @var list<array<string, mixed>> $emptyList */
            $emptyList = [];
            $this->crawl($nestedAnnotations, $link, $emptyList);

            return;
        }

        $isList = $this->isList($result);
        /** @var list<array<string, mixed>> $nestedBodyList */
        $nestedBodyList = $isList ? $result : [$result];

        // Recursively process nested level
        $this->crawl($nestedAnnotations, $link, $nestedBodyList);

        // Update body with nested results
        $body[$rel] = $isList ? $nestedBodyList : $nestedBodyList[0];
    }

    /**
     * Get Link annotations from a ResourceObject method using PHP 8 attributes
     *
     * @return list<Link>
     */
    private function getLinkAnnotations(ResourceObject $ro, string $method): array
    {
        $classMethod = 'on' . ucfirst($method);
        $refMethod = new ReflectionMethod($ro, $classMethod);
        $attributes = $refMethod->getAttributes(Link::class);

        return array_map(
            static fn ($attr) => $attr->newInstance(),
            $attributes,
        );
    }

    /**
     * Determine if the value is a list (multiple rows) or a single row
     *
     * List: [['id' => 1], ['id' => 2]] - crawl processes each row
     * Single row: ['id' => 1, 'name' => 'foo'] - crawl wraps in array, processes, unwraps
     */
    #[Override]
    public function isList(mixed $value): bool
    {
        assert(is_array($value));
        /** @var BodyList $list */
        $list = $value;
        /** @var mixed $firstRow */
        $firstRow = array_pop($list);
        $keys = array_keys((array) $firstRow);

        return $this->isSingleColumnList($value, $keys, $list)
            || $this->isMultiColumnMultiRowList($keys, $list)
            || $this->isMultiColumnList($value, $firstRow);
    }

    /**
     * Multiple rows with same column structure
     *
     * Example: [['id' => 1, 'name' => 'a'], ['id' => 2, 'name' => 'b']]
     *
     * @param list<array-key> $keys
     * @psalm-param BodyList   $list
     */
    private function isMultiColumnMultiRowList(array $keys, array $list): bool
    {
        if ($keys === [0 => 0]) {
            return false;
        }

        foreach ($list as $item) {
            if ($keys !== array_keys((array) $item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Numeric-indexed array where each element is an array
     *
     * Example: [0 => ['id' => 1], 1 => ['id' => 2]]
     *
     * @param array<mixed> $value
     */
    private function isMultiColumnList(array $value, mixed $firstRow): bool
    {
        return is_array($firstRow) && array_filter(array_keys($value), is_numeric(...)) === array_keys($value);
    }

    /**
     * Single element list
     *
     * Example: [0 => ['id' => 1]]
     *
     * @param array<mixed>    $value
     * @param list<array-key> $keys
     * @param array<mixed>    $list
     */
    private function isSingleColumnList(array $value, array $keys, array $list): bool
    {
        return (count($value) === 1) && $keys === array_keys($list);
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource\DataLoader;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Exception\RowMustContainKeyInDataLoaderException;
use BEAR\Resource\LinkType;
use BEAR\Resource\Types;
use Ray\Di\InjectorInterface;

use function array_key_exists;
use function assert;
use function explode;
use function implode;
use function is_string;
use function parse_str;
use function parse_url;
use function preg_match_all;
use function str_contains;
use function str_starts_with;
use function uri_template;

use const PHP_URL_QUERY;

/**
 * Loads data in batch for DataLoader-enabled links
 *
 * @psalm-import-type Query from Types
 * @psalm-import-type QueryList from Types
 * @psalm-import-type ObjectList from Types
 */
final class DataLoader
{
    /** @var array<class-string<DataLoaderInterface>, DataLoaderInterface> */
    private array $cache = [];

    /** @psalm-pure */
    public function __construct(
        private readonly InjectorInterface $injector,
    ) {
    }

    /**
     * Load data for DataLoader-enabled links
     *
     * @param ObjectList $annotations
     * @param QueryList  $bodyList
     *
     * @param-out QueryList $bodyList
     *
     * @psalm-suppress ReferenceConstraintViolation
     *
     * @psalm-impure
     */
    public function load(array $annotations, LinkType $link, array &$bodyList): void
    {
        foreach ($annotations as $annotation) {
            if (! $annotation instanceof Link || $annotation->crawl !== $link->key) {
                continue;
            }

            if ($annotation->dataLoader === null) {
                continue;
            }

            $this->processDataLoader($annotation, $bodyList);
        }
    }

    /**
     * @param QueryList $bodyList
     *
     * @param-out QueryList $bodyList
     *
     * @psalm-suppress ReferenceConstraintViolation
     *
     * @psalm-impure
     */
    private function processDataLoader(Link $annotation, array &$bodyList): void
    {
        // Extract keys from URI template (parameters with {placeholder} values)
        $keys = $this->extractKeysFromTemplate($annotation->href);

        // Generate URIs and extract queries
        /** @var list<array{index: int, query: array<string, string>}> $uriData */
        $uriData = [];
        foreach ($bodyList as $index => $body) {
            $uri = uri_template($annotation->href, $body);
            $query = $this->parseQuery($uri);
            $uriData[] = ['index' => $index, 'query' => $query];
        }

        // Collect queries for DataLoader
        $queries = [];
        foreach ($uriData as $data) {
            $queries[] = $data['query'];
        }

        // Call DataLoader
        assert($annotation->dataLoader !== null);
        $loader = $this->getDataLoader($annotation->dataLoader);
        $rows = $loader($queries);

        // Group rows by keys
        /** @var array<string, list<array<string, mixed>>> $grouped */
        $grouped = [];
        foreach ($rows as $row) {
            $groupKey = $this->buildGroupKey($row, $keys);
            $grouped[$groupKey][] = $row;
        }

        // Distribute results to bodyList
        foreach ($uriData as $data) {
            $groupKey = $this->buildGroupKeyFromQuery($data['query'], $keys);
            $bodyList[$data['index']][$annotation->rel] = $grouped[$groupKey] ?? [];
        }
    }

    /**
     * Extract key parameter names from URI template
     *
     * @return list<string> Parameter names that will be used for matching
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    private function extractKeysFromTemplate(string $template): array
    {
        return [...$this->extractQueryExpansionKeys($template), ...$this->extractEqualsFormatKeys($template)];
    }

    /**
     * Extract keys from {?var1,var2} or {&var} format
     *
     * @return list<string>
     *
     * @psalm-capabilities pure
     */
    private function extractQueryExpansionKeys(string $template): array
    {
        if (preg_match_all('/\{[?&]([^}]+)\}/', $template, $matches) === 0) {
            return [];
        }

        $keys = [];
        foreach ($matches[1] as $varList) {
            foreach (explode(',', $varList) as $var) {
                $keys[] = $var;
            }
        }

        return $keys;
    }

    /**
     * Extract keys from param={var} format
     *
     * @return list<string>
     *
     * @psalm-impure
     */
    private function extractEqualsFormatKeys(string $template): array
    {
        $queryString = parse_url($template, PHP_URL_QUERY);
        if (! is_string($queryString)) {
            return [];
        }

        if (! str_contains($queryString, '=')) {
            return [];
        }

        parse_str($queryString, $params);
        $keys = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($params as $name => $value) {
            if (! str_starts_with((string) $value, '{')) {
                continue;
            }

            $keys[] = (string) $name;
        }

        return $keys;
    }

    /**
     * Parse query string from URI
     *
     * @return array<string, string>
     *
     * @psalm-impure
     */
    private function parseQuery(string $uri): array
    {
        $queryString = parse_url($uri, PHP_URL_QUERY);
        if (! is_string($queryString)) {
            return [];
        }

        parse_str($queryString, $query);

        /** @var array<string, string> $query */
        return $query;
    }

    /**
     * Build a group key from row data
     *
     * @param array<string, mixed> $row
     * @param list<string>         $keys
     *
     * @psalm-capabilities pure
     */
    private function buildGroupKey(array $row, array $keys): string
    {
        $parts = [];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $row)) {
                throw new RowMustContainKeyInDataLoaderException($key);
            }

            $parts[] = (string) $row[$key];
        }

        return implode("\0", $parts);
    }

    /**
     * Build a group key from query parameters
     *
     * @param array<string, string> $query
     * @param list<string>          $keys
     *
     * @psalm-pure
     */
    private function buildGroupKeyFromQuery(array $query, array $keys): string
    {
        $parts = [];
        foreach ($keys as $key) {
            $parts[] = $query[$key] ?? '';
        }

        return implode("\0", $parts);
    }

    /**
     * Get or create a DataLoader instance
     *
     * @param class-string<DataLoaderInterface> $class
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    private function getDataLoader(string $class): DataLoaderInterface
    {
        if (! isset($this->cache[$class])) {
            $instance = $this->injector->getInstance($class);
            assert($instance instanceof DataLoaderInterface);
            $this->cache[$class] = $instance;
        }

        return $this->cache[$class];
    }
}

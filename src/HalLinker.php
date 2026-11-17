<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Nocarrier\Hal;

use function is_array;
use function parse_str;
use function parse_url;
use function uri_template;

use const PHP_URL_QUERY;

/**
 * @psalm-import-type Query from Types
 * @psalm-import-type Body from Types
 * @psalm-import-type HalLinks from Types
 * @psalm-import-type HalLinkData from Types
 */
final readonly class HalLinker
{
    /** @psalm-pure */
    public function __construct(
        private ReverseLinkerInterface $link,
    ) {
    }

    /** @param Query $query */
    public function getReverseLink(string $uri, array $query): string
    {
        return ($this->link)($uri, $query);
    }

    /**
     * Expand the link href with the body (RFC 6570) and return the reverse link
     *
     * The expanded query is passed to the reverse linker, as for the self link and `Location`.
     *
     * @param Body $body
     */
    public function expandHref(Link $link, array $body): string
    {
        $uri = uri_template($link->href, $body);
        parse_str((string) parse_url($uri, PHP_URL_QUERY), $query);
        /** @var Query $query */

        return $this->getReverseLink($uri, $query);
    }

    /**
     * @param Body         $body
     * @param list<object> $methodAnnotations
     */
    public function addHalLink(array $body, array $methodAnnotations, Hal $hal): Hal
    {
        if (! empty($methodAnnotations)) {
            $hal = $this->linkAnnotation($body, $methodAnnotations, $hal);
        }

        if (isset($body['_links'])) {
            /** @var array{_links: HalLinks} $body */
            $hal = $this->bodyLink($body, $hal);
        }

        return $hal;
    }

    /**
     * @param Body                   $body
     * @param non-empty-list<object> $methodAnnotations
     */
    private function linkAnnotation(array $body, array $methodAnnotations, Hal $hal): Hal
    {
        foreach ($methodAnnotations as $annotation) {
            if (! $annotation instanceof Link) {
                continue;
            }

            $reverseUri = $this->expandHref($annotation, $body);

            if (isset($body['_links']) && is_array($body['_links']) && isset($body['_links'][$annotation->rel])) {
                // skip if already difined links in ResourceObject
                continue;
            }

            $hal->addLink($annotation->rel, $reverseUri, $this->linkAttributes($annotation));
        }

        return $hal;
    }

    /**
     * @return array{method?: non-empty-string, title?: non-empty-string}
     *
     * @psalm-capabilities read-props
     */
    private function linkAttributes(Link $annotation): array
    {
        $attr = [];
        if ($annotation->method !== '' && $annotation->method !== Link::DEFAULT_METHOD) {
            $attr['method'] = $annotation->method;
        }

        if ($annotation->title !== '') {
            $attr['title'] = $annotation->title;
        }

        return $attr;
    }

    /**
     * @param array{_links: HalLinks} $body
     *
     * User can set `_links` array as a `Links` annotation
     */
    private function bodyLink(array $body, Hal $hal): Hal
    {
        foreach ($body['_links'] as $rel => $link) {
            if (! isset($link['href'])) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $attr = $link;
            unset($attr['href']);
            $hal->addLink($rel, $link['href'], $attr);
        }

        return $hal;
    }
}

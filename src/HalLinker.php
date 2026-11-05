<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Nocarrier\Hal;

use function is_array;
use function uri_template;

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

    /**
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    public function getReverseLink(string $uri, array $query): string
    {
        return ($this->link)($uri, $query);
    }

    /**
     * @param Body         $body
     * @param list<object> $methodAnnotations
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
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
     *
     * @psalm-impure
     */
    private function linkAnnotation(array $body, array $methodAnnotations, Hal $hal): Hal
    {
        foreach ($methodAnnotations as $annotation) {
            if (! $annotation instanceof Link) {
                continue;
            }

            $uri = uri_template($annotation->href, $body);
            $reverseUri = $this->getReverseLink($uri, []);

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
     *
     * @psalm-capabilities write-props|read-globals|write-globals|io|time
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

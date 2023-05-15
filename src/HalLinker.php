<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use Nocarrier\Hal;

use function is_string;
use function uri_template;

final class HalLinker
{
    public function __construct(
        private ReverseLinkerInterface $link,
    ) {
    }

    /** @param array<string, mixed> $query */
    public function getReverseLink(string $uri, array $query): string
    {
        return ($this->link)($uri, $query);
    }

    /**
     * @param array<mixed> $body
     * @param list<object> $methodAnnotations
     */
    public function addHalLink(array $body, array $methodAnnotations, Hal $hal): Hal
    {
        if (! empty($methodAnnotations)) {
            $hal = $this->linkAnnotation($body, $methodAnnotations, $hal);
        }

        if (isset($body['_links'])) {
            /** @var array{_links: array<string, array{href: string}>} $body */
            $hal = $this->bodyLink($body, $hal);
        }

        return $hal;
    }

    /**
     * @param array<int|string, mixed>|array{_links: string} $body
     * @param non-empty-list<object>                         $methodAnnotations
     */
    private function linkAnnotation(array $body, array $methodAnnotations, Hal $hal): Hal
    {
        foreach ($methodAnnotations as $annotation) {
            if (! $annotation instanceof Link) {
                continue;
            }

            $uri = uri_template($annotation->href, $body);
            $reverseUri = $this->getReverseLink($uri, []);

            if (isset($body['_links'][$annotation->rel])) { // @phpstan-ignore-line
                // skip if already difined links in ResourceObject
                continue;
            }

            $hal->addLink($annotation->rel, $reverseUri);
        }

        return $hal;
    }

    /**
     * @param array{_links: array<array{href?: string}>} $body
     *
     * User can set `_links` array as a `Links` annotation
     */
    private function bodyLink(array $body, Hal $hal): Hal
    {
        foreach ($body['_links'] as $rel => $link) {
            if (! is_string($rel) || ! isset($link['href'])) {
                continue;
            }

            $attr = $link;
            unset($attr['href']);
            $hal->addLink($rel, $link['href'], $attr);
        }

        return $hal;
    }
}

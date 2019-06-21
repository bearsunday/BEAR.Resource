<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;
use function is_string;
use Nocarrier\Hal;

final class HalLink
{
    /**
     * @var ReverseLinkInterface
     */
    private $link;

    public function __construct(ReverseLinkInterface $link)
    {
        $this->link = $link;
    }

    public function getReverseLink(string $uri) : string
    {
        return ($this->link)($uri);
    }

    public function addHalLink(array $body, array $methodAnnotations, Hal $hal) : Hal
    {
        if (! empty($methodAnnotations)) {
            $hal = $this->linkAnnotation($body, $methodAnnotations, $hal);
        }
        if (isset($body['_links'])) {
            $hal = $this->bodyLink($body, $hal);
        }

        return $hal;
    }

    private function linkAnnotation(array $body, array $methodAnnotations, Hal $hal) : Hal
    {
        foreach ($methodAnnotations as $annotation) {
            if (! $annotation instanceof Link) {
                continue;
            }
            $uri = uri_template($annotation->href, $body);
            $reverseUri = $this->getReverseLink($uri);
            if (isset($body['_links'][$annotation->rel])) {
                // skip if already difined links in ResourceObject
                continue;
            }
            $hal->addLink($annotation->rel, $reverseUri);
        }

        return $hal;
    }

    private function bodyLink(array $body, Hal $hal) : Hal
    {
        foreach ((array) $body['_links'] as $rel => $link) {
            if (is_string($rel) && isset($link['href'])) {
                $attr = $link;
                unset($attr['href']);
                $hal->addLink($rel, $link['href'], $attr);
            }
        }

        return $hal;
    }
}

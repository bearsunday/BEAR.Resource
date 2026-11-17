<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use BEAR\Resource\DataLoader\DataLoaderInterface;
use JsonSerializable;
use Override;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Link implements JsonSerializable
{
    public const DEFAULT_METHOD = 'get';

    /**
     * @param string                                 $href       URI template (RFC 6570)
     * @param string                                 $crawl      Crawl name that linkCrawl() follows
     * @param class-string<DataLoaderInterface>|null $dataLoader
     *
     * @psalm-pure
     */
    public function __construct(
        public string $rel = '',
        public string $href = '',
        public string $method = self::DEFAULT_METHOD,
        public string $title = '',
        public string $crawl = '',
        public string|null $dataLoader = null,
    ) {
    }

    /**
     * @return string[]
     * @psalm-return array{rel: string, href: string, method: string, title?: string}
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function jsonSerialize(): array
    {
        $json = [
            'rel' => $this->rel,
            'href' => $this->href,
            'method' => $this->method,
        ];
        if ($this->title) {
            $json += ['title' => $this->title];
        }

        return $json;
    }
}

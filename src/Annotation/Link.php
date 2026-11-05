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
     * Relation to the target resource of the link
     *
     * @var string
     */
    public $rel;

    /**
     * A URI template, as defined by RFC 6570
     *
     * @var string
     */
    public $href;

    /**
     * A method for the Link
     *
     * @var string
     */
    public $method;

    /**
     * A title for the link
     *
     * @var string
     */
    public $title;

    /**
     * Crawl tag ID for crawl request
     *
     * @var string
     */
    public $crawl;

    /**
     * DataLoader class for bulk processing
     *
     * @var class-string<DataLoaderInterface>|null
     */
    public $dataLoader;

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

    /**
     * @param array{rel?: string, href?: string, method?: string, title?: string, crawl?: string, dataLoader?: class-string<DataLoaderInterface>|null} $values
     *
     * @psalm-capabilities read-props
     */
    public function __construct(
        array $values = [],
        string $rel = '',
        string $href = '',
        string $method = self::DEFAULT_METHOD,
        string $title = '',
        string $crawl = '',
        string|null $dataLoader = null,
    ) {
        $this->rel = $values['rel'] ?? $rel;
        $this->href = $values['href'] ?? $href;
        $this->method = $values['method'] ?? $method;
        $this->title = $values['title'] ?? $title;
        $this->crawl = $values['crawl'] ?? $crawl;
        /** @var class-string<DataLoaderInterface>|null $resolvedDataLoader */
        $resolvedDataLoader = $values['dataLoader'] ?? $dataLoader;
        $this->dataLoader = $resolvedDataLoader;
    }
}

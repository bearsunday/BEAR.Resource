<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;
use JsonSerializable;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
final class Link implements JsonSerializable, NamedArgumentConstructorAnnotation
{
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
     * @Enum({"get", "post", "put", "patch", "delete"})
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
     * @return string[]
     * @psalm-return array{rel: string, href: string, method: string, title?: string}
     */
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
     * @param array{rel?: string, href?: string, method?: string, title?: string, crawl?:string} $values
     */
    public function __construct(
        array $values = [],
        string $rel = '',
        string $href = '',
        string $method = 'get',
        string $title = '',
        string $crawl = ''
    ) {
        $this->rel = $values['rel'] ?? $rel;
        $this->href = $values['href'] ?? $href;
        $this->method = $values['method'] ?? $method;
        $this->title = $values['title'] ?? $title;
        $this->crawl = $values['crawl'] ?? $crawl;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use BEAR\Resource\DataLoader\DataLoaderInterface;
use BEAR\Resource\Exception\UriException;
use JsonSerializable;
use Override;

use function preg_replace;
use function sprintf;
use function str_contains;

/**
 * A hyperlink to another resource, which linkSelf(), linkNew(), linkCrawl() and href() follow
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/resource_link.html#out-bound-links
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Link implements JsonSerializable
{
    public const DEFAULT_METHOD = 'get';

    /**
     * @param string                                 $href       URI template (RFC 6570) without a fragment
     * @param string                                 $crawl      Crawl name that linkCrawl() follows
     * @param class-string<DataLoaderInterface>|null $dataLoader Loads this rel for all rows of the crawl in one call
     *
     * @throws UriException
     */
    public function __construct(
        public string $rel = '',
        public string $href = '',
        public string $method = self::DEFAULT_METHOD,
        public string $title = '',
        public string $crawl = '',
        public string|null $dataLoader = null,
    ) {
        // A `#` outside the expressions, or the {#...} fragment operator, declares a fragment
        if (str_contains((string) preg_replace('/\{(?!#)[^}]*\}/', '', $href), '#')) {
            throw new UriException(sprintf('%s: a #[Link] href cannot have a fragment', $href), 500);
        }
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

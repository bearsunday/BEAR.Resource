<?php

declare(strict_types=1);

namespace BEAR\Resource;

use OutOfRangeException;
use Override;

use function array_merge;
use function in_array;

/**
 * @property $this $lazy
 * @property $this $eager
 * @psalm-suppress PropertyNotSetInConstructor for DSL
 * @psalm-import-type Query from Types
 */
final class Request extends AbstractRequest
{
    /** @deprecated Use Method::GET instead */
    public const GET = 'get';

    /** @deprecated Use Method::POST instead */
    public const POST = 'post';

    /** @deprecated Use Method::PUT instead */
    public const PUT = 'put';

    /** @deprecated Use Method::PATCH instead */
    public const PATCH = 'patch';

    /** @deprecated Use Method::DELETE instead */
    public const DELETE = 'delete';

    /** @deprecated Use Method::HEAD instead */
    public const HEAD = 'head';

    /** @deprecated Use Method::OPTIONS instead */
    public const OPTIONS = 'options';

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @psalm-suppress PossiblyUnusedReturnValue - Return value used implicitly via magic property access
     */
    #[Override]
    public function __get(string $name): mixed
    {
        if ($name === 'eager' || $name === 'lazy') {
            $this->in = $name;

            return $this;
        }

        if (in_array($name, ['code', 'headers', 'body'], true)) {
            return parent::__get($name);
        }

        throw new OutOfRangeException($name);
    }

    /**
     * {@inheritDoc}
     *
     * @param Query $query
     */
    #[Override]
    public function withQuery(array $query): RequestInterface
    {
        $this->query = $query;
        $this->result = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function addQuery(array $query): RequestInterface
    {
        $this->query = array_merge($this->query, $query);
        $this->result = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toUriWithMethod(): string
    {
        $uri = $this->toUri();

        return "{$this->method->value} {$uri}";
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toUri(): string
    {
        $this->resourceObject->uri->query = $this->query;

        return (string) $this->resourceObject->uri;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function linkSelf(string $linkKey): RequestInterface
    {
        $this->links[] = new LinkType($linkKey, LinkType::SELF_LINK);
        $this->result = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function linkNew(string $linkKey): RequestInterface
    {
        $this->links[] = new LinkType($linkKey, LinkType::NEW_LINK);
        $this->result = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function linkCrawl(string $linkKey): RequestInterface
    {
        $this->links[] = new LinkType($linkKey, LinkType::CRAWL_LINK);
        $this->result = null;

        return $this;
    }
}

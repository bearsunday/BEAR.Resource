<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @property $this $lazy
 * @property $this $eager
 *
 * @psalm-suppress PropertyNotSetInConstructor for DSL
 */
final class Request extends AbstractRequest
{
    const GET = 'get';

    const POST = 'post';

    const PUT = 'put';

    const PATCH = 'patch';

    const DELETE = 'delete';

    const HEAD = 'head';

    const OPTIONS = 'options';

    /**
     * @return array<string, string>|int|self
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function __get(string $name)
    {
        if ($name === 'eager' || $name === 'lazy') {
            $this->in = $name;

            return $this;
        }
        if (in_array($name, ['code', 'headers', 'body'], true)) {
            return parent::__get($name);
        }

        throw new \OutOfRangeException($name);
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string, mixed> $query
     */
    public function withQuery(array $query) : RequestInterface
    {
        $this->query = $query;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addQuery(array $query) : RequestInterface
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toUriWithMethod() : string
    {
        $uri = $this->toUri();

        return "{$this->method} {$uri}";
    }

    /**
     * {@inheritdoc}
     */
    public function toUri() : string
    {
        $this->resourceObject->uri->query = $this->query;

        return (string) $this->resourceObject->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function linkSelf(string $linkKey) : RequestInterface
    {
        $this->links[] = new LinkType($linkKey, LinkType::SELF_LINK);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function linkNew(string $linkKey) : RequestInterface
    {
        $this->links[] = new LinkType($linkKey, LinkType::NEW_LINK);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function linkCrawl(string $linkKey) : RequestInterface
    {
        $this->links[] = new LinkType($linkKey, LinkType::CRAWL_LINK);

        return $this;
    }
}

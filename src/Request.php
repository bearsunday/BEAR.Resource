<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

/**
 * @property $this $lazy
 * @property $this $eager
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
     * @param string $name
     *
     * @return $this|int|string|array
     */
    public function __get($name)
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
     */
    public function withQuery(array $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addQuery(array $query)
    {
        $this->query = array_merge($this->query, $query);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toUriWithMethod()
    {
        $uri = $this->toUri();

        return "{$this->method} {$uri}";
    }

    /**
     * {@inheritdoc}
     */
    public function toUri()
    {
        $this->ro->uri->query = $this->query;

        return (string) $this->ro->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function linkSelf($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::SELF_LINK);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function linkNew($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::NEW_LINK);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function linkCrawl($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::CRAWL_LINK);

        return $this;
    }
}

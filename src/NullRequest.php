<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @property int    $code
 * @property array  $headers
 * @property mixed  $body
 * @property string $view
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class NullRequest extends AbstractRequest
{
    public function __construct()
    {
        $this->resourceObject = new NullResourceObject;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery(array $query) : RequestInterface
    {
        unset($query);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addQuery(array $query) : RequestInterface
    {
        unset($query);

        return $this;
    }

    public function toUri() : string
    {
        return (string) new NullUri;
    }

    public function toUriWithMethod() : string
    {
        return 'get ' . (string) new NullUri;
    }

    /**
     * @return self
     */
    public function linkSelf(string $linkKey) : RequestInterface
    {
        unset($linkKey);

        return $this;
    }

    /**
     * @return self
     */
    public function linkNew(string $linkKey) : RequestInterface
    {
        unset($linkKey);

        return $this;
    }

    /**
     * @return self
     */
    public function linkCrawl(string $linkKey) : RequestInterface
    {
        unset($linkKey);

        return $this;
    }
}

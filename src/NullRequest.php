<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @property string $code
 * @property array  $headers
 * @property mixed  $body
 * @property string $view
 */
class NullRequest extends AbstractRequest
{
    public function __construct()
    {
        $this->resourceObject = new NullResourceObject;
    }

    public function withQuery(array $query) : RequestInterface
    {
        return $this;
    }

    public function addQuery(array $query) : RequestInterface
    {
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

    public function linkSelf(string $linkKey) : RequestInterface
    {
        return $this;
    }

    public function linkNew(string $linkKey) : RequestInterface
    {
        return $this;
    }

    public function linkCrawl(string $linkKey) : RequestInterface
    {
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeRequest implements RequestInterface
{
    public function __invoke(?array $query = null): ResourceObject
    {
        // TODO: Implement __invoke() method.
    }

    public function withQuery(array $query): RequestInterface
    {
    }

    public function addQuery(array $query): RequestInterface
    {
    }

    public function toUri(): string
    {
    }

    public function toUriWithMethod(): string
    {
    }

    public function hash(): string
    {
    }

    public function request()
    {
    }

    public function linkSelf(string $linkKey): RequestInterface
    {
    }

    public function linkNew(string $linkKey): RequestInterface
    {
    }

    public function linkCrawl(string $linkKey): RequestInterface
    {
    }
}

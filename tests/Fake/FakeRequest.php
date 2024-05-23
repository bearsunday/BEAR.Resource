<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeRequest implements RequestInterface
{
    /** @inheritDoc */
    public function __invoke(?array $query = null): ResourceObject
    {
    }

    /** @inheritDoc */
    public function withQuery(array $query): RequestInterface
    {
    }

    /** @inheritDoc */
    public function addQuery(array $query): RequestInterface
    {
    }

    /** @inheritDoc */
    public function toUri(): string
    {
    }

    /** @inheritDoc */
    public function toUriWithMethod(): string
    {
    }

    /** @inheritDoc */
    public function hash(): string
    {
    }

    /** @inheritDoc */
    public function request()
    {
    }

    /** @inheritDoc */
    public function linkSelf(string $linkKey): RequestInterface
    {
    }

    /** @inheritDoc */
    public function linkNew(string $linkKey): RequestInterface
    {
    }

    /** @inheritDoc */
    public function linkCrawl(string $linkKey): RequestInterface
    {
    }
}

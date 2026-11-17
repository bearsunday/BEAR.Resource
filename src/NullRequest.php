<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;

/** @psalm-import-type Query from Types */
final class NullRequest implements RequestInterface
{
    /**
     * @param Query $query
     *
     * @psalm-pure
     */
    #[Override]
    public function __invoke(array|null $query = null): ResourceObject
    {
        return new NullResourceObject();
    }

    /** @psalm-pure */
    #[Override]
    public function hash(): string
    {
        return '';
    }

    /** @psalm-pure */
    #[Override]
    public function request(): ResourceObject
    {
        return new NullResourceObject();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function withQuery(array $query): RequestInterface
    {
        unset($query);

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function addQuery(array $query): RequestInterface
    {
        unset($query);

        return $this;
    }

    /** @psalm-capabilities pure */
    #[Override]
    public function toUri(): string
    {
        return (string) new NullUri();
    }

    /** @psalm-capabilities pure */
    #[Override]
    public function toUriWithMethod(): string
    {
        return 'get ' . (string) new NullUri();
    }

    /**
     * @return self
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function linkSelf(string $linkKey): RequestInterface
    {
        unset($linkKey);

        return $this;
    }

    /**
     * @return self
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function linkNew(string $linkKey): RequestInterface
    {
        unset($linkKey);

        return $this;
    }

    /**
     * @return self
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function linkCrawl(string $linkKey): RequestInterface
    {
        unset($linkKey);

        return $this;
    }
}

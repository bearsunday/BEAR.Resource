<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @property AbstractRequest $eager
 * @property AbstractRequest $lazy
 * @psalm-import-type Query from Types
 */
interface RequestInterface
{
    /**
     * InvokerInterface resource request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function __invoke(array|null $query = null): ResourceObject;

    /**
     * Set query
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function withQuery(array $query): self;

    /**
     * Merge query
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function addQuery(array $query): self;

    /**
     * To Request URI string
     *
     * @psalm-capabilities read-props|write-props
     */
    public function toUri(): string;

    /**
     * To Request URI string with request method
     *
     * @psalm-capabilities read-props|write-props
     */
    public function toUriWithMethod(): string;

    /**
     * Return request hash
     *
     * @psalm-capabilities read-props|io
     */
    public function hash(): string;

    /**
     * @return AbstractRequest|ResourceObject
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function request();

    /**
     * Replace linked resource
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function linkSelf(string $linkKey): self;

    /**
     * Add linked resource
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function linkNew(string $linkKey): self;

    /**
     * Crawl resource with link key
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function linkCrawl(string $linkKey): self;
}

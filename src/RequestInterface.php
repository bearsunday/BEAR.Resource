<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @property AbstractRequest $eager
 * @property AbstractRequest $lazy
 */
interface RequestInterface
{
    /**
     * InvokerInterface resource request
     *
     * @param array<string, mixed> $query
     */
    public function __invoke(array|null $query = null): ResourceObject;

    /**
     * Set query
     *
     * @param array<string, mixed> $query
     */
    public function withQuery(array $query): self;

    /**
     * Merge query
     *
     * @param array<string, mixed> $query
     */
    public function addQuery(array $query): self;

    /**
     * To Request URI string
     */
    public function toUri(): string;

    /**
     * To Request URI string with request method
     */
    public function toUriWithMethod(): string;

    /**
     * Return request hash
     */
    public function hash(): string;

    /** @return ResourceObject|static */
    public function request();

    /**
     * Replace linked resource
     */
    public function linkSelf(string $linkKey): self;

    /**
     * Add linked resource
     */
    public function linkNew(string $linkKey): self;

    /**
     * Crawl resource with link key
     */
    public function linkCrawl(string $linkKey): self;
}

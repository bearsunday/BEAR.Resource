<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-import-type Query from Types */
interface ResourceInterface
{
    /**
     * Return new resource object instance
     *
     * @param AbstractUri|string $uri
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    public function newInstance($uri): ResourceObject;

    /**
     * Set resource object
     *
     * @psalm-capabilities read-props
     */
    public function object(ResourceObject $ro): RequestInterface;

    /**
     * Set URI
     *
     * @param AbstractUri|string $uri
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function uri($uri): RequestInterface;

    /**
     * Create a request atomically (coroutine-safe)
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function newRequest(Method $method, string $uri, array $query = []): RequestInterface;

    /**
     * Execute a GET request with link crawl
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function crawl(string $uri, string $linkKey, array $query = []): ResourceObject;

    /**
     * Hyper reference (Hypertext As The Engine Of Application State)
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function href(string $rel, array $query = [], ResourceObject|null $ro = null): ResourceObject;

    /**
     * Invoke GET request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function get(string $uri, array $query = []): ResourceObject;

    /**
     * Invoke POST request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function post(string $uri, array $query = []): ResourceObject;

    /**
     * Invoke PUT request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function put(string $uri, array $query = []): ResourceObject;

    /**
     * Invoke PATCH request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function patch(string $uri, array $query = []): ResourceObject;

    /**
     * Invoke DELETE request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function delete(string $uri, array $query = []): ResourceObject;

    /**
     * Invoke HEAD request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function head(string $uri, array $query = []): ResourceObject;

    /**
     * Invoke OPTIONS request
     *
     * @param Query $query
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function options(string $uri, array $query = []): ResourceObject;
}

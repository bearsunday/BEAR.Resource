<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @property $this $get
 * @property $this $post
 * @property $this $put
 * @property $this $patch
 * @property $this $delete
 * @property $this $head
 * @property $this $options
 */
interface ResourceInterface
{
    /**
     * Return new resource object instance
     *
     * @param AbstractUri|string $uri
     */
    public function newInstance($uri) : ResourceObject;

    /**
     * Set resource object
     */
    public function object(ResourceObject $ro) : RequestInterface;

    /**
     * Set URI
     *
     * @param AbstractUri|string $uri
     */
    public function uri($uri) : RequestInterface;

    /**
     * Hyper reference (Hypertext As The Engine Of Application State)
     *
     * @param array<string, mixed> $query
     */
    public function href(string $rel, array $query = []) : ResourceObject;

    /**
     * Invoke GET request
     *
     * @param array<string, mixed> $query
     */
    public function get(string $uri, array $query = []) : ResourceObject;

    /**
     * Invoke POST request
     *
     * @param array<string, mixed> $query
     */
    public function post(string $uri, array $query = []) : ResourceObject;

    /**
     * Invoke PUT request
     *
     * @param array<string, mixed> $query
     */
    public function put(string $uri, array $query = []) : ResourceObject;

    /**
     * Invoke PATCH request
     *
     * @param array<string, mixed> $query
     */
    public function patch(string $uri, array $query = []) : ResourceObject;

    /**
     * Invoke DELETE request
     *
     * @param array<string, mixed> $query
     */
    public function delete(string $uri, array $query = []) : ResourceObject;

    /**
     * Invoke HEAD request
     *
     * @param array<string, mixed> $query
     */
    public function head(string $uri, array $query = []) : ResourceObject;

    /**
     * Invoke OPTIONS request
     *
     * @param array<string, mixed> $query
     */
    public function options(string $uri, array $query = []) : ResourceObject;
}

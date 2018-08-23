<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
     * @param string|AbstractUri $uri
     */
    public function newInstance($uri) : ResourceObject;

    /**
     * Set resource object
     */
    public function object(ResourceObject $ro) : RequestInterface;

    /**
     * Set URI
     *
     * @param string|AbstractUri $uri
     */
    public function uri($uri) : RequestInterface;

    /**
     * Hyper reference (Hypertext As The Engine Of Application State)
     */
    public function href(string $rel, array $query = []) : ResourceObject;

    /**
     * Invoke GET request
     */
    public function get(string $uri, array $query) : ResourceObject;

    /**
     * Invoke POST request
     */
    public function post(string $uri, array $query) : ResourceObject;

    /**
     * Invoke PUT request
     */
    public function put(string $uri, array $query) : ResourceObject;

    /**
     * Invoke PATCH request
     */
    public function patch(string $uri, array $query) : ResourceObject;

    /**
     * Invoke DELETE request
     */
    public function delete(string $uri, array $query) : ResourceObject;

    /**
     * Invoke HEAD request
     */
    public function head(string $uri, array $query) : ResourceObject;
}

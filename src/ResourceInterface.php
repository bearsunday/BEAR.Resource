<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

/**
 * @property $this $get
 * @property $this $post
 * @property $this $patch
 * @property $this $put
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
}

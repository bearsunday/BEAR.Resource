<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @param string $uri
     *
     * @return ResourceObject
     */
    public function newInstance($uri);

    /**
     * Set resource object
     *
     * @param mixed $ro
     *
     * @return RequestInterface
     */
    public function object($ro);

    /**
     * Set URI
     *
     * @param string|AbstractUri $uri
     *
     * @return RequestInterface
     */
    public function uri($uri);

    /**
     * Hyper reference (Hypertext As The Engine Of Application State)
     *
     * @param string $rel
     * @param array  $query
     *
     * @return mixed
     */
    public function href($rel, array $query = []);
}

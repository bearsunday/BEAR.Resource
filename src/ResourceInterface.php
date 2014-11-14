<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface ResourceInterface
{
    /**
     * Return new resource object instance
     *
     * @param string $uri
     *
     * @return $this
     */
    public function newInstance($uri);

    /**
     * Set resource object
     *
     * @param mixed $ro
     *
     * @return ResourceObject
     */
    public function object($ro);

    /**
     * Set resource object created by URI.
     *
     * @param string | AbstractUri $uri
     *
     * @return $this
     */
    public function uri($uri);

    /**
     * Set named parameter query
     *
     * @param array $query
     *
     * @return $this
     */
    public function withQuery(array $query);

    /**
     * Add query
     *
     * @param array $query
     *
     * @return $this
     */
    public function addQuery(array $query);

    /**
     * Return Request
     *
     * @return Request | ResourceObject
     */
    public function request();

    /**
     * Link self
     *
     * @param string $linkKey
     *
     * @return $this
     */
    public function linkSelf($linkKey);

    /**
     * Link new
     *
     * @param string $linkKey
     *
     * @return $this
     */
    public function linkNew($linkKey);

    /**
     * Link crawl
     *
     * @param string $linkKey
     *
     * @return $this
     */
    public function linkCrawl($linkKey);

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

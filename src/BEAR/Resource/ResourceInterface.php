<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Di\ImplementedBy;

/**
 * Interface for resource client
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Resource")
 *
 */
interface ResourceInterface
{
    /**
     * Return new resource object instance
     *
     * @param string $uri
     *
     * @return self
     */
    public function newInstance($uri);

    /**
     * Set resource object
     *
     * @param mixed $ro
     *
     * @return AbstractObject
     */
    public function object($ro);

    /**
     * Set resource object created by URI.
     *
     * @param string $uri
     *
     * @return Resource
     */
    public function uri($uri);

    /**
     * Set named parameter query
     *
     * @param  array    $query
     *
     * @return Resource
     */
    public function withQuery(array $query);

    /**
     * Return Request
     *
     * @return mixed ( | Request)
     */
    public function request();

    /**
     * Link self
     *
     * @param string $linkKey
     *
     * @return mixed
     */
    public function linkSelf($linkKey);

    /**
     * Link new
     *
     * @param string $linkKey
     *
     * @return mixed
     */
    public function linkNew($linkKey);

    /**
     * Link crawl
     *
     * @param string $linkKey
     *
     * @return mixed
     */
    public function linkCrawl($linkKey);

    /**
     * Attach argument provider
     *
     * @param string   $signal
     * @param Callable $argProvider
     *
     * @return void
     */
    public function attachParamProvider($signal, Callable $argProvider);
}

<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource client
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
     * @return self
     */
    public function uri($uri);

    /**
     * Set named parameter query
     *
     * @param  array    $query
     *
     * @return self
     */
    public function withQuery(array $query);

    /**
     * Add query
     *
     * @param array $query
     *
     * @return self
     */
    public function addQuery(array $query);

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
     * @return self
     */
    public function linkSelf($linkKey);

    /**
     * Link new
     *
     * @param string $linkKey
     *
     * @return self
     */
    public function linkNew($linkKey);

    /**
     * Link crawl
     *
     * @param string $linkKey
     *
     * @return self
     */
    public function linkCrawl($linkKey);

    /**
     * Attach parameter provider
     *
     * @param                        $varName
     * @param ParamProviderInterface $provider
     *
     * @return self
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider);
}

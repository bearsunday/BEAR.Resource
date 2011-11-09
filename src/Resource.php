<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Resource client interface
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
interface Resource
{
    /**
     * Return new resource object instance
     *
     * @param string $uri
     * @param array $query named parameter query
     *
     * @return BEAR\Resource\Object
     */
    public function newInstance($uri, array $query = array());

    /**
     * Set resource object
     *
     * @param ResourceObject $ro
     */
    public function object(ResourceObject $ro);

    /**
     * Set resource object created by URI.
     *
     * @param string $uri
     */
    public function uri($uri);

    /**
     * Set named parameter query
     *
     * @param array $query
     */
    public function withQuery(array $query);

    /**
     * Return Request
     *
     * @return mixed ( | Request)
     */
    public function request();
}

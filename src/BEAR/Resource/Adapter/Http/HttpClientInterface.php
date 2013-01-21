<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\Http;

/**
 * Interface for Http resource adapter
 *
 * @package BEAR.Resource
 */
interface HttpClientInterface
{
    /**
     * Get
     *
     * @return self
     */
    public function onGet();

    /**
     * Post
     *
     * @return self
     */
    public function onPost();

    /**
     * Put
     *
     * @return self
     */
    public function onPut();

    /**
     * Delete
     *
     * @return self
     */
    public function onDelete();

    /**
     * Options
     *
     * @return self
     */
    public function onOptions();

    /**
     * Head
     *
     * @return self
     */
    public function onHead();
}

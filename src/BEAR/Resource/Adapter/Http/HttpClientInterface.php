<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\Http;

/**
 * Interface for Http resource adapter
 */
interface HttpClientInterface
{
    /**
     * Get
     *
     * @return $this
     */
    public function onGet();

    /**
     * Post
     *
     * @return $this
     */
    public function onPost();

    /**
     * Put
     *
     * @return $this
     */
    public function onPut();

    /**
     * Delete
     *
     * @return $this
     */
    public function onDelete();

    /**
     * Options
     *
     * @return $this
     */
    public function onOptions();

    /**
     * Head
     *
     * @return $this
     */
    public function onHead();
}

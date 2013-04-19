<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Adapter\AdapterInterface;

/**
 * Interface for resource client
 *
 * @package BEAR.Resource
 */
interface SchemeCollectionInterface
{
    /**
     * Set scheme
     *
     * @param $scheme
     *
     * @return SchemeCollection
     */
    public function scheme($scheme);

    /**
     * Set host
     *
     * @param $host
     *
     * @return SchemeCollection
     */
    public function host($host);

    /**
     * Set resource adapter
     *
     * @param AdapterInterface $adapter
     *
     * @return SchemeCollection
     */
    public function toAdapter(AdapterInterface $adapter);
}

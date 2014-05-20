<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Adapter\AdapterInterface;

/**
 * Interface for resource client
 */
interface SchemeCollectionInterface extends \IteratorAggregate
{
    /**
     * Set scheme
     *
     * @param string $scheme
     *
     * @return SchemeCollection
     */
    public function scheme($scheme);

    /**
     * Set host
     *
     * @param string $host
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

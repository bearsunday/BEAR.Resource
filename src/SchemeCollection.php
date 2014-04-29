<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use ArrayObject;
use BEAR\Resource\Adapter\AdapterInterface;

/**
 * Resource scheme collection
 */
class SchemeCollection extends ArrayObject implements SchemeCollectionInterface
{
    /**
     * Scheme
     *
     * @var string
     */
    private $scheme;

    /**
     * Host
     *
     * @var string
     */
    private $host;

    /**
     * Set scheme
     *
     * @param string $scheme
     *
     * @return SchemeCollection
     */
    public function scheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * Set host
     *
     * @param string $host
     *
     * @return SchemeCollection
     */
    public function host($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Set resource adapter
     *
     * @param AdapterInterface $adapter
     *
     * @return SchemeCollection
     */
    public function toAdapter(AdapterInterface $adapter)
    {
        $this[$this->scheme][$this->host] = $adapter;
        $this->scheme = $this->host = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return \ArrayIterator|\MultipleIterator|\Traversable
     */
    public function getIterator()
    {
        $iterator = new \AppendIterator;
        $schemeCollection = $this->getArrayCopy();
        foreach ($schemeCollection as $scheme) {
            foreach ($scheme as $adapter) {
                if ($adapter instanceof \IteratorAggregate) {
                    /** @var $adapter \IteratorAggregate */
                    $iterator->append($adapter->getIterator());
                }
            }
        }

        return $iterator;
    }
}

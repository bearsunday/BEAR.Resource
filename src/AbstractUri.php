<?php
/**
 * This file is part of the _package_ package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Resource;


abstract class AbstractUri
{
    /**
     * @var string
     */
    public $scheme;

    /**
     * @var string
     */
    public $host;

    /**
     * @var string
     */
    public $path;

    /**
     * Associative query array
     *
     * @var array
     */
    public $query = [];

    /**
     * Return URI string
     *
     * @return string
     */
    abstract public function __toString();
}
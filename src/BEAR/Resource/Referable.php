<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Interface for hyper refference
 *
 * @package BEAR.Resource
 */
interface Referable
{
    /**
     * Get hyper reference URI
     *
     * @param string         $rel
     * @param ResourceObject $ro
     */
    public function href($rel, ResourceObject $ro);
}

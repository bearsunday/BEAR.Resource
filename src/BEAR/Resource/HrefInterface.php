<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\ObjectInterface as ResourceObject;

/**
 * Interface for hyper reference
 *
 * @package BEAR.Resource
 */
interface HrefInterface
{
    /**
     * Get hyper reference URI
     *
     * @param                $rel
     * @param AbstractObject $ro
     *
     * @return mixed
     */
    public function href($rel, AbstractObject $ro);
}

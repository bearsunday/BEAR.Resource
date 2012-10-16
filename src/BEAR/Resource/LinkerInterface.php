<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;
use Ray\Di\Di\ImplementedBy;

/**
 * Interface for resource link
 *
 * @package BEAR.Resource
 *
 * @ImplementedBy("BEAR\Resource\Linker")
 */
interface LinkerInterface
{
    /**
     * InvokerInterface link
     *
     * @param ResourceObject  $ro
     * @param Request         $request
     * @param mixed           $linkValue
     *
     * @return mixed
     */
    public function invoke(ResourceObject $ro, Request $request, $linkValue);
}

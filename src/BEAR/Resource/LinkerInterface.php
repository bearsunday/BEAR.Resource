<?php
/**
 * BEAR.Resource
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
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @ImplementedBy("BEAR\Resource\Linker")
 */
interface LinkerInterface
{
    /**
     * InvokerInterface link
     *
     * @param ResourceObject $ro
     * @param array          $links
     * @param mixed          $linkValue resource output value
     *
     * @return mixed link result
     */
    public function invoke(ResourceObject $ro, Request $request, $linkValue);
}

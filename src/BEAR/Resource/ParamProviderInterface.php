<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for ParamProvider
 *
 * @package BEAR\Resource
 */
interface ParamProviderInterface
{
    /**
     * @param Param $param
     *
     * @return mixed
     */
    public function __invoke(Param $param);
}

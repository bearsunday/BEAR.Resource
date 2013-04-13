<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;


interface ParamProviderInterface
{
    public function __invoke(Param $param);
}
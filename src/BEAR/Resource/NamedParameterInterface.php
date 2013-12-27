<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\MethodInvocation;

interface NamedParameterInterface
{
    /**
     * Get arguments
     *
     * @param array $callable
     * @param array $query
     *
     * @return array
     */
    public function getArgs(array $callable, array $query);
}

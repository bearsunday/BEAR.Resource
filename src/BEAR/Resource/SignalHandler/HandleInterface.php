<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\SignalHandler;

use ReflectionParameter;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Definition;

/**
 * Interface for resource link
 *
 * @package BEAR.Resource
 */
interface HandleInterface
{
    /**
     * HandleInterface parameter signal
     *
     * @param mixed                      $return     handler provided return value
     * @param ReflectionParameter        $parameter  parameter reflection
     * @param ReflectiveMethodInvocation $invocation
     * @param Definition                 $definition
     *
     * @return mixed null | Signal::STOP
     *
     */
    public function __invoke(
        $return,
        ReflectionParameter $parameter,
        ReflectiveMethodInvocation $invocation,
        Definition $definition
    );
}

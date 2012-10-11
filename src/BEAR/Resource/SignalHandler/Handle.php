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
use Aura\Signal\Manager as Signal;

/**
 * Interface for resource link
 *
 * @package BEAR.Resource
 *
 */
interface Handle
{
    /**
     * Handle parameter signal
     *
     * @param mixed                      $return     handler provided return value
     * @param ReflectionParameter        $parameter  parameter reflection
     * @param ReflectiveMethodInvocation $invocation
     * @param Definition                 $invocation
     *
     * @return mixed null | Signal::STOP
     *
     */
    public function __invoke(
        $return,
        ReflectionParameter $parameter,
        ReflectiveMethodInvocation $invocation,
        Definition $invocation
    );
}

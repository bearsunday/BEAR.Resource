<?php
/**
 * BEAR.Resource
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
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 */
interface Handle
{
    /**
     * Handle parameter signal
     *
     * @param mixed                      $return     handler provided return value
     * @param ReflectionParameter        $parameter  parameter reflection
     * @param ReflectiveMethodInvocation $invovation Method invocation
     * @param Definition                 $definition Class definition
     *
     * @return null | Signal::STOP
     */
    public function __invoke(
        $return,
        $parameter,
        ReflectiveMethodInvocation $invovation,
        Definition $definition
    );
}

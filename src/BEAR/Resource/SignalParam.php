<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Signal\Manager as Signal;
use Ray\Aop\MethodInvocation;
use ReflectionParameter;
use Ray\Di\Di\Inject;

/**
 * Signal Parameter
 *
 * @package BEAR.Resource
 */
class SignalParam implements SignalParamsInterface
{
    /**
     * @var \Aura\Signal\Manager
     */
    private $signal;

    /**
     * @var Param
     */
    private $param;
    /**
     * @param Signal $signal
     * @param Param  $param
     *
     * @Inject
     */
    public function __construct(Signal $signal, Param $param)
    {
        $this->signal = $signal;
        $this->param = $param;
    }

    public function getArg(ReflectionParameter $parameter, MethodInvocation $invocation)
    {
        $param = clone $this->param;
        $results = $this->signal->send(
            $this,
            $parameter->name,
            $param->set($invocation, $parameter)
        );
        if (! $results->isStopped()) {
            $msg = '$' . "{$parameter->name} in " . get_class($invocation->getThis()) . '::' . $invocation->getMethod()->name;
            throw new Exception\Parameter($msg);
        }

        return $param->getArg();
    }

    public function attachParamProvider($varName, ParamProviderInterface $provider)
    {
        $this->signal->handler('*', $varName, $provider);
    }
}

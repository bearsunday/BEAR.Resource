<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Signal\Manager as Signal;
use Ray\Aop\MethodInvocation;
use ReflectionParameter;
use Ray\Di\Di\Inject;

/**
 * Signal Parameter
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

    /**
     * {@inheritdoc}
     */
    public function getArg(ReflectionParameter $parameter, MethodInvocation $invocation)
    {
        $param = clone $this->param;
        $results = $this->sendSignal($parameter->name, $parameter, $param, $invocation, $parameter);
        if ($results->isStopped()) {
            return $param->getArg();
        }
        $results = $this->sendSignal('*', $parameter, $param, $invocation, $parameter);
        if ($results->isStopped()) {
            return $param->getArg();
        }
        $msg = '$' . "{$parameter->name} in " . get_class($invocation->getThis()) . '::' . $invocation->getMethod()->name;
        throw new Exception\Parameter($msg);
    }

    private function sendSignal($sigName, $parameter, $param, $invocation, $parameter)
    {
        $results = $this
            ->signal
            ->send(
                $this,
                $sigName,
                $param->set($invocation, $parameter)
            );

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider)
    {
        /** @noinspection PhpParamsInspection */
        $this->signal->handler('*', $varName, $provider);
    }
}

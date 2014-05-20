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
class SignalParameter implements SignalParameterInterface
{
    /**
     * @var Signal
     */
    private $signal;

    /**
     * @var Param
     */
    private $param;

    /**
     * @param Signal         $signal
     * @param ParamInterface $param
     *
     * @Inject
     */
    public function __construct(Signal $signal, ParamInterface $param)
    {
        $this->signal = $signal;
        $this->param = $param;
    }

    /**
     * {@inheritdoc}
     */
    public function getArg(ReflectionParameter $parameter, MethodInvocation $invocation)
    {
        try {
            $param = clone $this->param;
            $results = $this->sendSignal($parameter->name, $parameter, $param, $invocation);
            if ($results->isStopped()) {
                return $param->getArg();
            }
            $results = $this->sendSignal('*', $parameter, $param, $invocation);
            if ($results->isStopped()) {
                return $param->getArg();
            }

            // parameter not found
            $msg = '$' . "{$parameter->name} in " . get_class($invocation->getThis()) . '::' . $invocation->getMethod()->name;
            throw new Exception\Parameter($msg);
        } catch (\Exception $e) {
            // exception in provider
            throw new Exception\SignalParameter($e->getMessage(), 0, $e);
        }
    }

    /**
     * Send signal parameter
     *
     * @param string              $sigName
     * @param ReflectionParameter $parameter
     * @param ParamInterface      $param
     * @param MethodInvocation    $invocation
     *
     * @return \Aura\Signal\ResultCollection
     */
    private function sendSignal(
        $sigName,
        ReflectionParameter $parameter,
        ParamInterface $param,
        MethodInvocation $invocation
    ) {
        $results = $this->signal->send(
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

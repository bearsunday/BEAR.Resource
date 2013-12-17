<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Signal\Manager as Signal;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception\Parameter;
use BEAR\Resource\Exception\SignalParameter;
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
        try {
            $param = clone $this->param;
            $results = $this->sendSignal($parameter->name, $parameter, $param, $invocation, $parameter);
            if ($results->isStopped()) {
                return $param->getArg();
            }
            $results = $this->sendSignal('*', $parameter, $param, $invocation, $parameter);
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
     * @param Param               $param
     * @param MethodInvocation    $invocation
     * @param ReflectionParameter $parameter
     *
     * @return \Aura\Signal\ResultCollection
     */
    private function sendSignal(
        $sigName,
        ReflectionParameter $parameter,
        Param $param,
        MethodInvocation $invocation,
        ReflectionParameter $parameter
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

<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Di\ConfigInterface;
use Aura\Signal\Manager as Signal;
use Ray\Aop\Interceptor;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Aop\Weave;
use Ray\Aop\Weaver;
use ReflectionParameter;
use ReflectionMethod;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Config;
use ReflectionException;
use BEAR\Resource\Exception\MethodNotAllowed;
use BEAR\Resource\Annotation\ParamSignal;


/**
 * Reflective Parameter
 *
 * @package BEAR.Resource
 */
final class NamedParams implements MethodInterceptor
{
    /**
     * @var SignalParamsInterface
     */
    private $signalParam;

    /**
     * @return Signal
     */
    public function getSignal()
    {
        return $this->signal;
    }

    /**
     * @param SignalParamsInterface $signal
     */
    public function __construct(SignalParamsInterface $signalParam)
    {
        $this->signalParam = $signalParam;
    }

    /**
     * Return parameters
     *
     * @param object $object
     * @param string $method
     * @param array  $namedArgs
     *
     * @return array
     * @throws Exception\MethodNotAllowed
     */
    public function invoke(MethodInvocation $invocation, Weave $weave = null)
    {
        $object = $invocation->getThis();
        $namedArgs = $invocation->getArguments();
        $method = $invocation->getMethod();
        $parameters = $method->getParameters();
        $args = [];
        foreach ($parameters as $parameter) {
            /** @var $parameter \ReflectionParameter */
            if (isset($namedArgs[$parameter->name])) {
                $args[] = $namedArgs[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $args[] = $parameter->getDefaultValue();
            } else {
                $args[] = $this->signalParam->getArg($parameter, $invocation);
            }
        }
        $object = $weave ?: $object;
        $result = call_user_func_array([$object, $method->name], $args);

        return $result;
    }

    public function attachParamProvider($varName, ParamProviderInterface $provider)
    {
        $this->signalParam->attachParamProvider($varName, $provider);
    }
}

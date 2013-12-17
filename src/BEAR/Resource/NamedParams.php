<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception;
use Ray\Aop\MethodInvocation;
use ReflectionParameter;
use Ray\Di\Di\Inject;

/**
 * Reflective Parameter
 */
final class NamedParams implements NamedParamInterface
{
    /**
     * @var SignalParamsInterface
     */
    private $signalParam;

    /**
     * @param SignalParamsInterface $signalParam
     *
     * @Inject(optional=true)
     */
    public function __construct(SignalParamsInterface $signalParam = null)
    {
        $this->signalParam = $signalParam;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
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
                continue;
            }
            if ($parameter->isDefaultValueAvailable() === true) {
                $args[] = $parameter->getDefaultValue();
                continue;
            }
            if ($this->signalParam) {
                $args[] = $this->signalParam->getArg($parameter, $invocation);
                continue;
            }
            $msg = '$' . "{$parameter->name} in " . get_class($invocation->getThis()) . '::' . $invocation->getMethod()->name;
            throw new Exception\Parameter($msg);
        }

        try {
            $result = call_user_func_array([$object, $method->name], $args);
        } catch (Exception\Parameter $e) {
            throw new Exception\ParameterInService('', 0, $e);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider)
    {
        $this->signalParam->attachParamProvider($varName, $provider);
    }
}

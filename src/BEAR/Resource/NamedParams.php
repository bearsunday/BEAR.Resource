<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Aop\MethodInvocation;
use Ray\Aop\Weave;
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
     * @Inject
     */
    public function __construct(SignalParamsInterface $signalParam)
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
            $args[] = $this->signalParam->getArg($parameter, $invocation);
        }
        $result = call_user_func_array([$object, $method->name], $args);

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

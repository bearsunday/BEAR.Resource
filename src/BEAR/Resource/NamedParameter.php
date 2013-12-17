<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception;
use Ray\Aop\MethodInvocation;
use Ray\Aop\ReflectiveMethodInvocation;
use ReflectionParameter;
use Ray\Di\Di\Inject;

/**
 * Reflective Parameter
 */
final class NamedParameter implements NamedParameterInterface
{
    /**
     * @var SignalParamsInterface
     */
    private $signalParameter;

    /**
     * @param SignalParamsInterface $signalParameter
     *
     * @Inject(optional=true)
     */
    public function __construct(SignalParamsInterface $signalParameter = null)
    {
        $this->signalParameter = $signalParameter;
    }

    /**
     * {@inheritdoc}
     */
    public function getArgs(array $callable, array $query)
    {
        $namedArgs = $query;
        $method = new \ReflectionMethod($callable[0], $callable[1]);
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
            if ($this->signalParameter) {
                $invocation = new ReflectiveMethodInvocation($callable, $query);
                $args[] = $this->signalParameter->getArg($parameter, $invocation);
                continue;
            }
            $msg = '$' . "{$parameter->name} in " . get_class($callable[0]) . '::' . $callable[1] . '()';
            throw new Exception\Parameter($msg);
        }

        return $args;
    }

    /**
     * {@inheritdoc}
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider)
    {
        $this->signalParameter->attachParamProvider($varName, $provider);
    }
}

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
final class ReflectiveParams
{
    /**
     * ProviderInterface annotation
     *
     * @var string
     */
    const ANNOTATION_PROVIDES = 'Provides';

    const SIGNAL_PARAM = 'param';


    private $config;
    private $signal;

    public function __construct(ConfigInterface $config, Signal $signal, Invoker $invoker)
    {
        $this->config = $config;
        $this->signal = $signal;
        $this->invoker = $invoker;
    }

    public function getParams($object, $method, array $args)
    {
        try {
            $parameters = (new \ReflectionMethod($object, $method))->getParameters();
        } catch (ReflectionException $e) {
            throw new MethodNotAllowed;
        }
        if ($parameters === []) {
            return [];
        }
        $providesArgs = [];
        $params = [];
        foreach ($parameters as $parameter) {
            /** @var $parameter \ReflectionParameter */
            if (isset($args[$parameter->name])) {
                $params[] = $args[$parameter->name];
            } elseif ($parameter->isDefaultValueAvailable() === true) {
                $params[] = $parameter->getDefaultValue();
            } elseif (isset($providesArgs[$parameter->name])) {
                $params[] = $providesArgs[$parameter->name];
            } else {
                $result = $this->getArgumentBySignal($parameter, $object, $method, $args);
                if ($result->args) {
                    $providesArgs = $result->args;
                }
                $params[] = $result->value;
            }
        }

        return $params;
    }

    /**
     * Return argument from provider or signal handler
     *
     * This method called when client and service object both has sufficient argument
     *
     * @param \ReflectionParameter $parameter
     * @param  object              $object
     * @param string               $method
     * @param array                $args
     *
     * @return Result
     * @throws Exception\Parameter
     */
    private function getArgumentBySignal(ReflectionParameter $parameter, $object, $method, array $args)
    {
        $definition = $this->config->fetch(get_class($object))[Config::INDEX_DEFINITION];
        /** @var $definition \Ray\Di\Definition */
        $userAnnotation = $definition->getUserAnnotationByMethod($method);
        $signalAnnotations = isset($userAnnotation['ParamSignal']) ? $userAnnotation['ParamSignal'] : [];
        $signalIds = ['Provides'];
        foreach ($signalAnnotations as $annotation) {
            if ($annotation instanceof ParamSignal) {
                $signalIds[] = $annotation->value;
            }
        }
        $return = new Result;
        if (!$signalIds) {
            goto PARAMETER_NOT_PROVIDED;
        }
        foreach ($signalIds as $signalId) {
            $results = $this->signal->send(
                $this,
                ReflectiveParams::SIGNAL_PARAM . $signalId,
                $return,
                $parameter,
                new ReflectiveMethodInvocation([$object, $method], $args, $signalAnnotations),
                $definition
            );
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $isStopped = $results->isStopped();
        $result = $results->getLast();
        if ($isStopped === false || is_null($result)) {
            goto PARAMETER_NOT_PROVIDED;
        }
        PARAMETER_PROVIDED:

        return $return;


        PARAMETER_NOT_PROVIDED:
        /** @noinspection PhpUnreachableStatementInspection */
        $msg = '$' . "{$parameter->name} in " . get_class($object) . '::' . $method;
        throw new Exception\Parameter($msg);
    }
}

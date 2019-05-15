<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use function get_class;
use function is_array;
use Ray\Aop\WeavedInterface;
use Ray\Di\InjectorInterface;

final class NamedParameter implements NamedParameterInterface
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var NamedParamMetasInterface
     */
    private $paramMetas;

    public function __construct(NamedParamMetasInterface $paramMetas, InjectorInterface $injector)
    {
        $this->paramMetas = $paramMetas;
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(callable $callable, array $query) : array
    {
        $metas = ($this->paramMetas)($callable);
        $parameters = [];
        foreach ($metas as $varName => $param) {
            /* @var $param ParamInterface */
            $parameters[] = $param($varName, $query, $this->injector);
        }

        return $parameters;
    }

    private function getErrorMessage(callable $callable, ParameterException $e) : string
    {
        if (is_array($callable) && count($callable) === 2) {
            $object = $callable[0];
            $class = $callable[0] instanceof WeavedInterface ? (new \ReflectionClass($object))->getParentClass()->getName() : get_class($object);

            return sprintf('%s in %s::%s', $e->getMessage(), $class, (string) $callable[1]);
        }

        return sprintf('%s', $e->getMessage());
    }
}

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
}

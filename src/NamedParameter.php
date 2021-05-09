<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class NamedParameter implements NamedParameterInterface
{
    /** @var InjectorInterface */
    private $injector;

    /** @var NamedParamMetasInterface */
    private $paramMetas;

    public function __construct(NamedParamMetasInterface $paramMetas, InjectorInterface $injector)
    {
        $this->paramMetas = $paramMetas;
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(callable $callable, array $query): array
    {
        $metas = ($this->paramMetas)($callable);
        $parameters = [];
        /** @var array<string, array<string, mixed>> $query */
        foreach ($metas as $varName => $param) { // @phpstan-ignore-line
            /** @psalm-suppress MixedAssignment */
            $parameters[] = $param($varName, $query, $this->injector);
        }

        return $parameters;
    }
}

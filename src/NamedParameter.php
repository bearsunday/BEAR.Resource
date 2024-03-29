<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class NamedParameter implements NamedParameterInterface
{
    public function __construct(
        private readonly NamedParamMetasInterface $paramMetas,
        private readonly InjectorInterface $injector,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(callable $callable, array $query): array
    {
        $metas = ($this->paramMetas)($callable);
        $parameters = [];
        foreach ($metas as $varName => $param) {
            /** @psalm-suppress all */
            $parameters[$varName] = $param($varName, $query, $this->injector);
        }

        return $parameters;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class NamedParameter implements NamedParameterInterface
{
    public function __construct(
        private NamedParamMetasInterface $paramMetas,
        private InjectorInterface $injector,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(callable $callable, array $query): array
    {
        $metas = ($this->paramMetas)($callable);
        $parameters = [];
        foreach ($metas as $varName => $param) {
            /** @psalm-suppress all */
            $parameters[] = $param($varName, $query, $this->injector); // @phpstan-ignore-line
        }

        return $parameters;
    }
}

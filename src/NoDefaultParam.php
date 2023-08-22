<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Di\InjectorInterface;

final class NoDefaultParam implements ParamInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        unset($query, $injector);

        throw new ParameterException($varName);
    }
}

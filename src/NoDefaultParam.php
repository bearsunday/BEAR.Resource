<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Override;
use Ray\Di\InjectorInterface;

final class NoDefaultParam implements ParamInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities pure
     */
    #[Override]
    public function __invoke(string $varName, array $query, InjectorInterface $injector): never
    {
        unset($query, $injector);

        throw new ParameterException($varName);
    }
}

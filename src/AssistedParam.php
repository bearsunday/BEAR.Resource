<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class AssistedParam implements ParamInterface
{
    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector): void
    {
        unset($varName, $query, $injector);
    }
}

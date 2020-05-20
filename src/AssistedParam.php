<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class AssistedParam implements ParamInterface
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        unset($varName, $query, $injector);
    }
}

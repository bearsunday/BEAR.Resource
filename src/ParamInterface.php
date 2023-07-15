<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

interface ParamInterface
{
    /**
     * @param array<string, mixed> $query
     *
     * @return mixed
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector);
}

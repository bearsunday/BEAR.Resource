<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

/** @psalm-import-type Query from Types */
interface ParamInterface
{
    /**
     * @param Query $query
     *
     * @return mixed
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector);
}

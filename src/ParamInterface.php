<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

interface ParamInterface
{
    /**
     * @param string $varName
     * @param array  $query
     *
     * @return mixed
     */
    public function __invoke($varName, array $query, InjectorInterface $injector);
}

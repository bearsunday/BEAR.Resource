<?php declare(strict_types=1);
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
     * @return mixed
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector);
}

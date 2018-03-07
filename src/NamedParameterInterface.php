<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface NamedParameterInterface
{
    /**
     * Return ordered parameters from named query
     */
    public function getParameters(callable $callable, array $query) : array;
}

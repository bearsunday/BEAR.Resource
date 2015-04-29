<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface NamedParameterInterface
{
    /**
     * Get arguments
     *
     * @param array $callable
     * @param array $query
     *
     * @return array
     */
    public function getParameters(array $callable, array $query);
}

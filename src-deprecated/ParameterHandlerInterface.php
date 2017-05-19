<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

/**
 * @deprecated
 */
interface ParameterHandlerInterface
{
    /**
     * Handle insufficient parameter
     *
     * @param \ReflectionParameter $parameter
     * @param array                $query
     *
     * @return mixed
     */
    public function handle(\ReflectionParameter $parameter, array $query);
}

<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface ParamHandlerInterface
{
    /**
     * Handle insufficient parameter
     *
     * @param \ReflectionParameter $parameter
     *
     * @return mixed
     */
    public function handle(\ReflectionParameter $parameter);
}

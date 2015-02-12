<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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

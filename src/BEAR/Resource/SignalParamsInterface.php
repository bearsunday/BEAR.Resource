<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Resource;

use ReflectionParameter;
use Ray\Aop\MethodInvocation;

interface SignalParamsInterface
{
    /**
     * Return single argument by signal
     *
     * @param ReflectionParameter $parameter
     * @param MethodInvocation    $invocation
     *
     * @return mixed
     */
    public function getArg(ReflectionParameter $parameter, MethodInvocation $invocation);

    /**
     * Attach parameter provider
     *
     * @param string                 $varName
     * @param ParamProviderInterface $provider
     *
     * @return self
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider);
}

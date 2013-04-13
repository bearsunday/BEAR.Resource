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
    public function getArg(ReflectionParameter $parameter, MethodInvocation $invocation);
    public function attachParamProvider($varName, ParamProviderInterface $provider);
}

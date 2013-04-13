<?php

namespace BEAR\Resource;

use Ray\Aop\MethodInvocation;
use ReflectionParameter;

/**
 * This file is part of the {package} package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
interface ParamInterface
{
    public function set(MethodInvocation $invocation, ReflectionParameter $parameter);

    public function getMethodInvocation();

    public function getParameter();

    public function inject($arg);
}

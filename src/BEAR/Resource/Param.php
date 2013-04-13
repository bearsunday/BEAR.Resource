<?php

namespace BEAR\Resource;

/**
 * This file is part of the {package} package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

use Ray\Aop\MethodInvocation;
use ReflectionParameter;
use Aura\Signal\Manager;

final class Param implements ParamInterface
{
    /**
     * @var MethodInvocation
     */
    private $invocation;

    /**
     * @var ReflectionParameter
     */
    private $parameter;

    public function set(MethodInvocation $invocation, ReflectionParameter $parameter)
    {
        $this->invocation = $invocation;
        $this->parameter = $parameter;

        return $this;
    }

    public function getMethodInvocation()
    {
        return $this->invocation;
    }

    public function getParameter()
    {
        return $this->parameter;
    }

    public function inject($arg)
    {
        $this->arg = $arg;
        return Manager::STOP;
    }

    public function getArg()
    {
        return $this->arg;
    }
}

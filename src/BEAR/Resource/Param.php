<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Ray\Aop\MethodInvocation;
use ReflectionParameter;

/**
 * This file is part of the {package} package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
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

    /**
     * @var mixed
     */
    private $arg;

    /**
     * {@inheritdoc}
     */
    public function set(MethodInvocation $invocation, ReflectionParameter $parameter)
    {
        $this->invocation = $invocation;
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodInvocation()
    {
        return $this->invocation;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function inject($arg)
    {
        $this->arg = $arg;
        return Manager::STOP;
    }

    /**
     * {@inheritdoc}
     */
    public function getArg()
    {
        return $this->arg;
    }
}

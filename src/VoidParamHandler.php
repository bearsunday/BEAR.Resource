<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Aop\WeavedInterface;

class VoidParamHandler implements ParamHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle(\ReflectionParameter $parameter)
    {
        $class = $parameter->getDeclaringClass();
        $className = $class->implementsInterface(WeavedInterface::class) ? $class->getParentClass()->getName() : $class->name;
        $method = $parameter->getDeclaringFunction()->name;
        $msg = sprintf("$%s in %s::%s()", $parameter->name, $className, $method);

        throw new ParameterException($msg, Code::BAD_REQUEST);
    }
}

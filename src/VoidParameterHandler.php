<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Aop\WeavedInterface;

class VoidParameterHandler implements ParameterHandlerInterface
{
    /**
     * {@inheritdoc}
     *
     * @throws ParameterException
     */
    public function handle(\ReflectionParameter $parameter, array $query)
    {
        unset($query);
        $class = $parameter->getDeclaringClass();
        $className = $class->implementsInterface(WeavedInterface::class) ? $class->getParentClass()->getName() : $class->name;
        $method = $parameter->getDeclaringFunction()->name;
        $msg = sprintf('$%s in %s::%s()', $parameter->name, $className, $method);

        throw new ParameterException($msg, Code::BAD_REQUEST);
    }
}

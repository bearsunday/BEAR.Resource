<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Aop\WeavedInterface;

final class ClassFileName
{
    public function __invoke($object) : string
    {
        if (! $object instanceof WeavedInterface) {
            return get_class($object);
        }

        $class = new \ReflectionClass($object);
        if (! $class instanceof \ReflectionClass) {
            throw new \ReflectionException;
        }
        $parent = $class->getParentClass();
        if (! $parent instanceof \ReflectionClass) {
            throw new \ReflectionException;
        }

        return (string) $parent->getFileName();
    }
}

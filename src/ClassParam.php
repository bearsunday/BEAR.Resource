<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Di\InjectorInterface;
use ReflectionClass;

final class ClassParam implements ParamInterface
{
    /**
     * @var ReflectionClass
     */
    private $class;

    /**
     * @var \ReflectionParameter
     */
    private $parameter;

    public function __construct(ReflectionClass $class, \ReflectionParameter $parameter)
    {
        $this->class = $class;
        $this->parameter = $parameter;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        try {
            $props = $this->getProps($varName, $query, $injector);
        } catch (ParameterException $e) {
            if ($this->parameter->isDefaultValueAvailable()) {
                return $this->parameter->getDefaultValue();
            }

            throw $e;
        }
        $obj = $this->class->newInstanceWithoutConstructor();
        foreach ($props as $propName => $propValue) {
            $obj->{$propName} = $propValue;
        }

        return $obj;
    }

    private function getProps(string $varName, array $query, InjectorInterface $injector) : array
    {
        if (isset($query[$varName])) {
            return $query[$varName];
        }
        // try camelCase variable name
        $snakeName = ltrim(strtolower(preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (isset($query[$snakeName])) {
            return $query[$snakeName];
        }

        unset($injector);

        throw new ParameterException($varName);
    }
}

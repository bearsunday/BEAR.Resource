<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function class_exists;
use Ray\Di\InjectorInterface;
use ReflectionClass;

final class ClassParam implements ParamInterface
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var bool
     */
    private $isDefaultAvailable;

    /**
     * @var mixed
     */
    private $defaultValue;

    public function __construct(ReflectionClass $class, \ReflectionParameter $parameter)
    {
        $this->class = $class->name;
        $this->isDefaultAvailable = $parameter->isDefaultValueAvailable();
        if ($this->isDefaultAvailable) {
            $this->defaultValue = $parameter->getDefaultValue();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        assert(class_exists($this->class));
        $obj = new $this->class;
        foreach ($query as $queryName => $queryValue) {
            if (property_exists($obj, $queryName)) {
                $obj->{$queryName} = $queryValue;

                continue;
            }
            $camelName = lcfirst(strtr(ucwords(strtr($queryName, ['_' => ' '])), [' ' => '']));
            if (property_exists($obj, $camelName)) {
                $obj->{$camelName} = $queryValue;

                continue;
            }
        }

        return $obj;
    }
}

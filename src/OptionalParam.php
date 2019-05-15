<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class OptionalParam implements ParamInterface
{
    private $defaultValue;

    public function __construct($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        unset($injector);
        if (isset($query[$varName])) {
            return $query[$varName];
        }
        // try camelCase variable name
        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (isset($query[$snakeName])) {
            return $query[$snakeName];
        }

        return $this->defaultValue;
    }
}

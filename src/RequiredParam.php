<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Di\InjectorInterface;

final class RequiredParam implements ParamInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        if (isset($query[$varName])) {
            return $query[$varName];
        }
        // try camelCase variable name
        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (isset($query[$snakeName])) {
            return $query[$snakeName];
        }
        unset($injector);

        throw new ParameterException($varName);
    }
}

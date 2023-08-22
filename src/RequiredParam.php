<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use Ray\Di\InjectorInterface;

use function array_key_exists;
use function ltrim;
use function preg_replace;
use function strtolower;

final class RequiredParam implements ParamInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        if (array_key_exists($varName, $query)) {
            return $query[$varName];
        }

        // try camelCase variable name
        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');
        if (array_key_exists($snakeName, $query)) {
            return $query[$snakeName];
        }

        unset($injector);

        throw new ParameterException($varName);
    }
}

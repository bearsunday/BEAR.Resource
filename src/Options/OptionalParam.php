<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\ParamInterface;
use Ray\Di\InjectorInterface;

use function ltrim;
use function preg_replace;
use function strtolower;

/** @template T */
final class OptionalParam implements ParamInterface
{
    /** @param T $defaultValue */
    public function __construct(
        private $defaultValue,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        unset($injector);
        if (isset($query[$varName])) {
            return $query[$varName];
        }

        // try camelCase variable name
        $snakeName = ltrim(strtolower((string) preg_replace('/[A-Z]/', '_\0', $varName)), '_');

        return $query[$snakeName] ?? $this->defaultValue;
    }
}

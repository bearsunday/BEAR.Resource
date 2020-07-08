<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

/**
 * @template T
 */
final class DefaultParam implements ParamInterface
{
    /**
     * @var T
     */
    private $defaultValue;

    /**
     * @param T $defaultValue
     */
    public function __construct($defaultValue)
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(string $varName, array $query, InjectorInterface $injector)
    {
        unset($varName, $query, $injector);

        return $this->defaultValue;
    }
}

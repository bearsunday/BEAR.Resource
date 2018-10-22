<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class DefaultParam implements ParamInterface
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
        unset($varName, $query, $injector);

        return $this->defaultValue;
    }
}

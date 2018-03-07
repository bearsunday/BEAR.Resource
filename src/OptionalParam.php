<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
    public function __invoke($varName, array $query, InjectorInterface $injector)
    {
        unset($injector);
        if (isset($query[$varName])) {
            return $query[$varName];
        }

        return $this->defaultValue;
    }
}

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
        unset($injector);
        if (isset($query[$varName])) {
            return $query[$varName];
        }

        throw new ParameterException($varName);
    }
}

<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class AssistedParam implements ParamInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke($varName, array $query, InjectorInterface $injector)
    {
        unset($varName, $query, $injector);

        return null;
    }
}

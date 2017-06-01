<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
    public function __invoke($varName, array $query, InjectorInterface $injector)
    {
        unset($query, $injector);

        return $this->defaultValue;
    }
}

<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\SchemeException;
use Ray\Di\AbstractModule;

class ContextualModule
{
    /**
     * @var string
     */
    private $defaultContextName;

    /**
     * @param string $defaultContextName
     */
    public function __construct($defaultContextName)
    {
        $this->defaultContextName = $defaultContextName;
    }

    /**
     * @param string $context
     * @param string $name
     *
     * @return null|AbstractModule
     */
    public function __invoke($context, $name)
    {
        $contextsArray = array_reverse(explode('-', $context));
        $module = null;
        foreach ($contextsArray as $context) {
            $class = $name . '\Module\\' . ucwords($context) . 'Module';
            if (! class_exists($class)) {
                $class = $this->defaultContextName . '\\' . ucwords($context) . 'Module';
            }
            if (! is_a($class, AbstractModule::class, true)) {
                throw new SchemeException($class);
            }
            /* @var $module AbstractModule */
            $module = new $class($module);
        }

        return $module;
    }
}

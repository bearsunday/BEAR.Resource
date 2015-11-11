<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class AppAdapter implements AdapterInterface
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * Resource adapter namespace
     *
     * @var string
     */
    private $namespace;

    /**
     * Resource adapter path
     *
     * @var string
     */
    private $path;

    /**
     * @param InjectorInterface $injector  Application dependency injector
     * @param string            $namespace Resource adapter namespace
     */
    public function __construct(InjectorInterface $injector, $namespace)
    {
        $this->injector = $injector;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function get(AbstractUri $uri)
    {
        if (substr($uri->path, -1) === '/') {
            $uri->path .= 'index';
        }
        $schemePath = ucwords($uri->scheme) . str_replace('-', '', ucwords($uri->path, '/-'));
        $namespace = str_replace('/', '\\', $schemePath);
        $class = sprintf('%s%s\Resource\%s', $this->namespace, $this->path, $namespace);
        $instance = $this->injector->getInstance($class);

        return $instance;
    }
}

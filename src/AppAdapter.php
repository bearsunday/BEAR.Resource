<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use Ray\Di\Exception\Unbound;
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
        // dirty hack for hhvm bug https://github.com/facebook/hhvm/issues/6368
        $path = ! defined('HHVM') ? str_replace('-', '', ucwords($uri->path, '/-')) : str_replace(' ', '\\', substr(ucwords(str_replace('/', ' ', ' ' . str_replace(' ', '', ucwords(str_replace('-', ' ', $uri->path))))), 1));
        $class = sprintf(
            '%s%s\Resource\%s',
            $this->namespace,
            $this->path,
            str_replace('/', '\\', ucwords($uri->scheme) . $path)
        );
        try {
            $instance = $this->injector->getInstance($class);
        } catch (Unbound $e) {
            $unboundClass = $e->getMessage();
            if  ($unboundClass === "{$class}-") {
                throw new ResourceNotFoundException($uri, 404, $e);
            }
            throw $e;
        }

        return $instance;
    }
}

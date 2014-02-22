<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\Exception\AppNamespace;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;

/**
 * Application resource adapter
 */
class App implements AdapterInterface
{
    /**
     * Application dependency injector
     *
     * @var \Ray\Di\Injector
     */
    private $injector;

    /**
     * Resource adapter namespace
     *
     * @var array
     */
    private $namespace;

    /**
     * Resource adapter path
     *
     * @var array
     */
    private $path;

    /**
     * @param InjectorInterface $injector  Application dependency injector
     * @param string            $namespace Resource adapter namespace
     * @param string            $path      Resource adapter path
     *
     * @Inject
     * @throws AppNamespace
     */
    public function __construct(
        InjectorInterface $injector,
        $namespace,
        $path
    ) {
        if (!is_string($namespace)) {
            throw new AppNamespace(gettype($namespace));
        }
        $this->injector = $injector;
        $this->namespace = $namespace;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function get($uri)
    {
        $parsedUrl = parse_url($uri);
        $path = str_replace('/', ' ', $parsedUrl['path']);
        $path = ucwords($path);
        $path = str_replace(' ', '\\', $path);
        $className = "{$this->namespace}\\{$this->path}{$path}";
        $instance = $this->injector->getInstance($className);

        return $instance;
    }
}

<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Provider;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Scope;
use RuntimeException;
use Exception;

/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 *
 * @Scope("prototype")
 */
class App implements ResourceObject, Provider, Adapter
{
    /**
     * Application dependency injector
     *
     * @var Injector
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
     * Constructor
     *
     * @param InjectorInterface $injector  Application dependency injector
     * @param string            $namespace Resource adapter namespace
     * @param string            $path      Resource adapter path
     *
     * @Inject
     */
    public function __construct(
            InjectorInterface $injector,
            $namespace,
            $path
    ){
        if (! is_string($namespace)) {
            throw new RuntimeException('namespace not string');
        }
        $this->injector = $injector;
        $this->namespace = $namespace;
        $this->path = $path;
    }

    /**
     * (non-PHPdoc)
     *
     * @see    BEAR\Resource.Provider::get()
     * @return object
     * @throws Exception\InvalidHost
     */
    public function get($uri)
    {
        $parsedUrl = parse_url($uri);
        $path = str_replace('/', ' ', $parsedUrl['path']);
        $path = ucwords($path);
        $path = str_replace(' ', '\\', $path);
        $host = $parsedUrl['host'];
        $className = "{$this->namespace}\\{$this->path}{$path}";
        $instance = $this->injector->getInstance($className);

        return $instance;
    }
}

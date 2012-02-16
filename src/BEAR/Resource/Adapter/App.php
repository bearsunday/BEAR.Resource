<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use Ray\Di\InjectorInterface,
    BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\Provider,
    BEAR\Resource\Exception,
    BEAR\Resource\Linkable;

/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("prototype")
 */
class App implements ResourceObject, Provider
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
     * @param string		    $path      Resource adapter path
     *
     * @Inject
     */
    public function __construct(
        InjectorInterface $injector,
        $namespace,
        $path
    ){
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
        $path = str_replace('/', '\\', $parsedUrl['path']);
        $host = $parsedUrl['host'];
        $className = "{$this->namespace}\\{$this->path}{$path}";
        $instance = $this->injector->getInstance($className);
        return $instance;
    }
}

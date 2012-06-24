<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Provider;
use BEAR\Resource\Exception\ResourceNotFound;
use Ray\Di\InjectorInterface;
use Ray\Di\Di\Inject;
use RuntimeException;
use Exception;

/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
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
        try {
            $instance = $this->injector->getInstance($className);
        } catch (\Doctrine\Common\Annotations\AnnotationException $e) {
            throw $e;
        } catch (Exception $e) {
            echo $e;
            throw new ResourceNotFound("$uri ($className)", 400, $e);
        }

        return $instance;
    }
}

<?php
/**
 * BEAR.Resource
 *
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
 * @Scope("singleton")
 */
class App implements ResourceObject, Provider
{
    /**
     * Config key for 'ResourceObject'
     *
     * @var string
     */
    const CONFIG_RO_FOLDER = 'ro_folder';

    /**
     * Class config
     *
     * @var array
     */
    public $config = array(self::CONFIG_RO_FOLDER => 'ResourceObject');


    /**
     * @Inject
     * @Named("path=ro_path,namespace=ro_namespace");
     */
    public function __construct(InjectorInterface $injector, array $namespace)
    {
        $this->injector = $injector;
        $this->namespace = $namespace;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Provider::get()
     * @throws Exception\InvalidHost
     * @return object;
     */
    public function get($uri)
    {
        $parsedUrl = parse_url($uri);
        $path = str_replace('/', '\\', $parsedUrl['path']);
        $host = $parsedUrl['host'];
        if (!isset($this->namespace[$host])) {
            throw new Exception\InvalidHost($host);
        }
        $className = "{$this->namespace[$host]}\\{$this->config[self::CONFIG_RO_FOLDER]}{$path}";
        $instance = $this->injector->getInstance($className);
        return $instance;
    }
}

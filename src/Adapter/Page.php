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
    BEAR\Resource\Exception;

/**
 * Page resource (page:://self/path/to/resource)
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Page extends App
{
    /**
     * Class config
     *
     * @var array
     */
    public $config = array(self::CONFIG_RO_FOLDER => 'Page');

    /**
     * Constructor
     * 
     * @param InjectorInterface $injector
     * @param array             $namespace [$scheme => $namespace][]
     * 
     * @Inject
     * @Named("path=ro_path,namespace=ro_namespace");
     */
    public function __construct(InjectorInterface $injector, array $namespace)
    {
        $this->injector = $injector;
        $this->namespace = $namespace;
    }
}

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
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Http implements ResourceObject
{
    public $uri;

    /**
     * @Inject
     * @Named("path=ro_path,namespace=ro_namespace");
     */
    public function __construct(InjectorInterface $injector, array $namespace)
    {
    }

    public function onGet(array $query)
    {
        v($this->uri);
    }
}

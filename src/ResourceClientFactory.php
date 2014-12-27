<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Ray\Di\Injector;
use Doctrine\Common\Annotations\Reader;

class ResourceClientFactory
{
    /**
     * @param string           $namespace
     * @param Reader           $reader
     * @param SchemeCollection $scheme
     *
     * @return \BEAR\Resource\Resource
     */
    public function newInstance($namespace, Reader $reader, SchemeCollection $scheme = null)
    {
        $injector = new Injector;
        $scheme = $scheme ?: $scheme = (new SchemeCollection)
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, $namespace, 'Resource\App'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, $namespace, 'Resource\Page'));
        $invoker = new Invoker(new NamedParameter);
        $factory = new Factory($scheme);
        $resource = new Resource(
            $factory,
            $invoker,
            new Anchor($reader),
            new Linker($reader, $invoker, $factory)
        );

        return $resource;
    }
}

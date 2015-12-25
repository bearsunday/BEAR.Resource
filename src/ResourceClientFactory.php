<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Di\InjectorInterface;

class ResourceClientFactory
{
    /**
     * @param InjectorInterface $injector
     * @param string            $nameSpace
     * @param SchemeCollection  $scheme
     *
     * @return Resource
     */
    public function newInstance(InjectorInterface $injector, $nameSpace, SchemeCollection $scheme = null)
    {
        $reader = new AnnotationReader();
        $scheme = $scheme ?: (new SchemeCollection)
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, $nameSpace, 'Resource\App'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, $nameSpace, 'Resource\Page'));
        $invoker = new Invoker(new NamedParameter(new ArrayCache(), new VoidParamHandler));
        $factory = new Factory($scheme);
        $resource = new ResourceClient(
            $factory,
            $invoker,
            new Anchor($reader),
            new Linker($reader, $invoker, $factory)
        );

        return $resource;
    }
}

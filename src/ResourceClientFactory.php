<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\AbstractModule;
use Ray\Di\EmptyModule;
use Ray\Di\Injector;
use Doctrine\Common\Annotations\Reader;

/**
 * @deprecated
 */
class ResourceClientFactory
{
    /**
     * @param string         $tmpDir
     * @param string         $namespace
     * @param AbstractModule $module
     *
     * @return ResourceInterface
     */
    public function newClient($tmpDir, $namespace, AbstractModule $module = null, SchemeCollection $scheme = null, AnnotationReader $reader = null)
    {
        $module = $module ?: new EmptyModule;
        $module->install(new ResourceModule($namespace));
        $injector = new Injector($module, $tmpDir);
        $reader = $reader ?: new AnnotationReader;
        $scheme = $scheme ?: $scheme = (new SchemeCollection)
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($injector, $namespace, 'Resource\App'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($injector, $namespace, 'Resource\Page'));

        return $this->newInstance($scheme, $reader);
    }

    /**
     * @param SchemeCollection $scheme
     *
     * @param Reader           $reader
     *
     * @return Resource
     * @internal   param string $namespace
     */
    private function newInstance(SchemeCollection $scheme = null, Reader $reader)
    {
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

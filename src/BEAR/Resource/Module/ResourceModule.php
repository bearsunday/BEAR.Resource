<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;
use Ray\Di\Module\InjectorModule;
use Ray\Di\Scope;

/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
class ResourceModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('BEAR\Resource\ResourceInterface')->to('BEAR\Resource\Resource')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\InvokerInterface')->to('BEAR\Resource\Invoker')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\LinkerInterface')->to('BEAR\Resource\Linker')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\LoggerInterface')->annotatedWith("resource_logger")->to('BEAR\Resource\Logger');
        $this->bind('BEAR\Resource\HrefInterface')->to('BEAR\Resource\A');
        $this->bind('BEAR\Resource\SignalParamsInterface')->to('BEAR\Resource\SignalParam');
        $this->bind('BEAR\Resource\FactoryInterface')->to('BEAR\Resource\Factory')->in(Scope::SINGLETON);
        $this
            ->bind('BEAR\Resource\SchemeCollectionInterface')
            ->toProvider('BEAR\Resource\Module\SchemeCollectionProvider')
            ->in(Scope::SINGLETON);
        $this
            ->bind('Aura\Signal\Manager')
            ->toProvider('BEAR\Resource\Module\SignalProvider')
            ->in(Scope::SINGLETON);
        $this
            ->bind('Guzzle\Parser\UriTemplate\UriTemplateInterface')
            ->to('Guzzle\Parser\UriTemplate\UriTemplate');
    }
}

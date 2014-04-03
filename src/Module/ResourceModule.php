<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use Ray\Di\AbstractModule;
use Ray\Di\Module\InjectorModule;
use Ray\Di\Scope;

class ResourceModule extends AbstractModule
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @param string $appName
     */
    public function __construct($appName)
    {
        $this->appName = $appName;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // install Injector
        $this->install(new InjectorModule($this));
        // bind app name
        if ($this->appName) {
            $this->bind()->annotatedWith('app_name')->toInstance($this->appName);
        }

        // bind resource client component
        $this->bind('BEAR\Resource\ResourceInterface')->to('BEAR\Resource\Resource')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\InvokerInterface')->to('BEAR\Resource\Invoker')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\LinkerInterface')->to('BEAR\Resource\Linker')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\LoggerInterface')->annotatedWith("resource_logger")->to('BEAR\Resource\Logger');
        $this->bind('BEAR\Resource\HrefInterface')->to('BEAR\Resource\A');
        $this->bind('BEAR\Resource\SignalParameterInterface')->to('BEAR\Resource\SignalParameter');
        $this->bind('BEAR\Resource\FactoryInterface')->to('BEAR\Resource\Factory')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\SchemeCollectionInterface')->toProvider('BEAR\Resource\Module\SchemeCollectionProvider')->in(Scope::SINGLETON);
        $this->bind('Aura\Signal\Manager')->toProvider('BEAR\Resource\Module\SignalProvider')->in(Scope::SINGLETON);
        $this->bind('Guzzle\Parser\UriTemplate\UriTemplateInterface')->to('Guzzle\Parser\UriTemplate\UriTemplate')->in(Scope::SINGLETON);
        $this->bind('BEAR\Resource\ParamInterface')->to('BEAR\Resource\Param');
        $this->bind('BEAR\Resource\Renderer\RendererInterface')->to('BEAR\Resource\Renderer\JsonRenderer');
        $this->bind('Ray\Di\InstanceInterface')->toInstance($this->dependencyInjector);
    }
}

<?php

namespace BEAR\Resource;

use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;
use Ray\Di\Injector;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;

/**
 * Framework default module
 */
class DefaultModule extends AbstractModule
{
    const APP_NAME = 'sandbox';
    
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $injector = Injector::create([$this]);;
        $this->bind('Ray\Di\InjectorInterface')->toInstance($injector);
        $this->bind('Aura\Di\ConfigInterface')->toInstance($injector->getContainer()->getForge()->getConfig());
        $this->bind('BEAR\Resource\Resource')->to('BEAR\Resource\Resource');
        $this->bind('BEAR\Resource\InvokerInterface')->to('BEAR\Resource\Invoker');
        $this->bind('Doctrine\Common\Annotations\Reader')->to('Doctrine\Common\Annotations\AnnotationReader');
        $this->bind('Aura\Signal\Manager')->toInstance(new Manager(new HandlerFactory, new ResultFactory, new ResultCollection));
        $appAdapter = new Adapter\App($injector, self::APP_NAME, 'Resource\App');
        $scheme = (new SchemeCollection)->scheme('app')->host('self')->toAdapter($appAdapter);
        $this->bind('BEAR\Resource\SchemeCollection')->toInstance($scheme);
    }
}

return new DefaultModule;
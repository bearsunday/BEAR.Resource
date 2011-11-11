<?php

namespace BEAR\Framework;

use Ray\Di\AbstractModule,
    Ray\Di\InjectorInterface;

/**
 * Framework default module
 */
class FrameworkModule extends AbstractModule
{
    public function __construct(InjectorInterface $injector){
        $this->injector = $injector;
        $this->configure();
    }

    protected function configure()
    {
        $this->bind('Ray\Di\InjectorInterface')->toInstance($this->injector);
        $this->bind('Ray\Di\ConfigInterface')->toInstance($this->injector->getContainer()->getForge()->getConfig());
        $this->bind('BEAR\Resource\Resource')->to('BEAR\Resource\Client');
        $this->bind('BEAR\Resource\Invoke')->to('BEAR\Resource\Invoker');
    }
}
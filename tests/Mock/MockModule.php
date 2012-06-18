<?php

namespace BEAR\Resource\Mock;

use Ray\Di\AbstractModule,
    Ray\Di\InjectorInterface;

/**
 * Framework default module
 */
class MockModule extends AbstractModule
{
    public function __construct(InjectorInterface $injector){
        $this->injector = $injector;
        $this->configure();
    }

    protected function configure()
    {
        $this->bind('Ray\Di\InjectorInterface')->toInstance($this->injector);
        $this->bind('Aura\Di\ConfigInterface')->toInstance($this->injector->getContainer()->getForge()->getConfig());
        $this->bind('BEAR\Resource\Resource')->to('BEAR\Resource\Resource');
        $this->bind('BEAR\Resource\InvokerInterface')->to('BEAR\Resource\Invoker');
    }
}
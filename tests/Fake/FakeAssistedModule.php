<?php

namespace BEAR\Resource;

use Ray\Di\AbstractModule;

class FakeAssistedModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('login_id')->toInstance('assisted01');
    }
}

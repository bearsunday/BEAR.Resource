<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\AbstractModule;

class FakeAssistedModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind()->annotatedWith('login_id')->toInstance('assisted01');
    }
}

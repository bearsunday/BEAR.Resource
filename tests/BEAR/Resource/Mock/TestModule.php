<?php

namespace BEAR\Resource\Mock;

use Ray\Di\AbstractModule;
use BEAR\Resource\Interceptor\Log;

/**
 * Framework default module
 */
class TestModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Resource\Annotation\Log'),
            [new Log]
        );
    }
}

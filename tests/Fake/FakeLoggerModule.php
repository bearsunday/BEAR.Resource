<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\Interceptor\FakeLogInterceptor;
use Ray\Di\AbstractModule;

class FakeLoggerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Resource\Annotation\Log'),
            [FakeLogInterceptor::class]
        );
    }
}

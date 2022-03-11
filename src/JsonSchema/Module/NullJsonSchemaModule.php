<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Interceptor\JsonSchemaInterceptorInterface;
use Ray\Aop\NullInterceptor;
use Ray\Di\AbstractModule;

final class NullJsonSchemaModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(JsonSchemaInterceptorInterface::class)->to(NullInterceptor::class);
    }
}

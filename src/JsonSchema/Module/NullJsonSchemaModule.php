<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Interceptor\JsonSchemaInterceptorInterface;
use Override;
use Ray\Aop\NullInterceptor;
use Ray\Di\AbstractModule;

final class NullJsonSchemaModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(JsonSchemaInterceptorInterface::class)->to(NullInterceptor::class);
    }
}

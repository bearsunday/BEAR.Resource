<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\JsonSchemaExceptionFakeHandler;
use BEAR\Resource\JsonSchemaExceptionHandlerInterface;
use Override;
use Ray\Di\AbstractModule;

final class FakeJsonModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(JsonSchemaExceptionHandlerInterface::class)->to(JsonSchemaExceptionFakeHandler::class);
    }
}

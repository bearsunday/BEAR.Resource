<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\JsonSchemaExceptionFakeHandler;
use BEAR\Resource\JsonSchemaExceptionHandlerInterface;
use Ray\Di\AbstractModule;

class FakeJsonModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(JsonSchemaExceptionHandlerInterface::class)->to(JsonSchemaExceptionFakeHandler::class);
    }
}

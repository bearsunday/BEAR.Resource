<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Interceptor\JsonSchemaInterceptor;
use BEAR\Resource\Interceptor\JsonSchemaInterceptorInterface;
use BEAR\Resource\JsonSchemaExceptionHandlerInterface;
use BEAR\Resource\JsonSchemaExceptionNullHandler;
use BEAR\Resource\ResourceObject;
use Ray\Di\AbstractModule;

final class JsonSchemaModule extends AbstractModule
{
    /** @var string */
    private $jsonSchemaDir;

    /** @var string */
    private $jsonValidateDir;

    /**
     * @param string $jsonSchemaDir   Json-schema json file directory
     * @param string $jsonValidateDir Json-schema validator json file directory
     */
    public function __construct(
        string $jsonSchemaDir = '',
        string $jsonValidateDir = '',
        ?AbstractModule $module = null
    ) {
        $this->jsonSchemaDir = $jsonSchemaDir;
        $this->jsonValidateDir = $jsonValidateDir;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith('json_schema_dir')->toInstance($this->jsonSchemaDir);
        $this->bind()->annotatedWith('json_validate_dir')->toInstance($this->jsonValidateDir);
        $this->bind(JsonSchemaExceptionHandlerInterface::class)->to(JsonSchemaExceptionNullHandler::class);
        $this->bind(JsonSchemaInterceptorInterface::class)->to(JsonSchemaInterceptor::class);
        $this->bindInterceptor(
            $this->matcher->subclassesOf(ResourceObject::class),
            $this->matcher->annotatedWith(JsonSchema::class),
            [JsonSchemaInterceptorInterface::class]
        );
    }
}

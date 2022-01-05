<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Exception\InvalidSchemaUriException;
use Ray\Di\AbstractModule;

use function filter_var;

use const FILTER_VALIDATE_URL;

final class JsonSchemaLinkHeaderModule extends AbstractModule
{
    /** @var string */
    private $jsonSchemaHost;

    /**
     * @param string $jsonSchemaHost Json-schema host name ex) https://example.com/schema/
     */
    public function __construct(
        string $jsonSchemaHost,
        ?AbstractModule $module = null
    ) {
        $this->jsonSchemaHost = $jsonSchemaHost;
        if (! filter_var($jsonSchemaHost, FILTER_VALIDATE_URL)) {
            throw new InvalidSchemaUriException($jsonSchemaHost);
        }

        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith('json_schema_host')->toInstance($this->jsonSchemaHost);
    }
}

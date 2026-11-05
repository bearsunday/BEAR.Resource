<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Exception\InvalidSchemaUriException;
use Override;
use Ray\Di\AbstractModule;

use function filter_var;

use const FILTER_VALIDATE_URL;

final class JsonSchemaLinkHeaderModule extends AbstractModule
{
    /**
     * @param string $jsonSchemaHost Json-schema host name ex) https://example.com/schema/
     *
     * @psalm-impure
     */
    public function __construct(
        private readonly string $jsonSchemaHost,
        AbstractModule|null $module = null,
    ) {
        if (! filter_var($jsonSchemaHost, FILTER_VALIDATE_URL)) {
            throw new InvalidSchemaUriException($jsonSchemaHost);
        }

        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind()->annotatedWith('json_schema_host')->toInstance($this->jsonSchemaHost);
    }
}

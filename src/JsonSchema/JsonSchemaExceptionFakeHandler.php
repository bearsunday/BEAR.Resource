<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;
use JSONSchemaFaker\Faker;
use LogicException;
use Override;
use SplFileInfo;
use stdClass;

use function class_exists;
use function is_object;

/** @psalm-import-type Body from Types */
final class JsonSchemaExceptionFakeHandler implements JsonSchemaExceptionHandlerInterface
{
    final public const X_FAKE_JSON = 'X-Fake-JSON';
    final public const X_JSON_SCHEMA_EXCEPTION = 'X-JSON-Schema-Exception';

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    public function handle(ResourceObject $ro, JsonSchemaException $e, string $schemaFile)
    {
        $ro->headers[self::X_FAKE_JSON] = $schemaFile;
        $ro->headers[self::X_JSON_SCHEMA_EXCEPTION] = $e->getMessage();
        $ro->body = $this->fakeResponse($schemaFile);
        $ro->view = null;
    }

    /**
     * @return Body
     *
     * @psalm-impure
     */
    private function fakeResponse(string $schemaFile): array
    {
        if (! class_exists(Faker::class)) {
            throw new LogicException('"koriym/json-schema-faker" not installed. Please run "composer require koriym/json-schema-faker --dev". See more at https://github.com/bearsunday/BEAR.Resource/wiki/json_schema_faker_required'); // @codeCoverageIgnore
        }

        /** @var Body $fakeObject */
        $fakeObject = (new Faker())->generate(new SplFileInfo($schemaFile));

        return $this->deepArray($fakeObject);
    }

    /**
     * @param Body $values
     *
     * @return Body
     *
     * @psalm-capabilities read-props
     */
    private function deepArray(array|stdClass $values): array
    {
        $result = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($values as $key => $value) { //@phpstan-ignore-line
            $result[$key] = is_object($value) ? $this->deepArray((array) $value) : $result[$key] = $value;
        }

        return $result;
    }
}

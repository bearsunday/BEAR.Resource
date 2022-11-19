<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;
use JSONSchemaFaker\Faker;
use SplFileInfo;
use stdClass;

use function is_object;

class JsonSchemaExceptionFakeHandler implements JsonSchemaExceptionHandlerInterface
{
    public const X_FAKE_JSON = 'X-Fake-JSON';

    public const X_JSON_SCHEMA_EXCEPTION = 'X-JSON-Schema-Exception';

    /**
     * {@inheritdoc}
     */
    public function handle(ResourceObject $ro, JsonSchemaException $e, string $schemaFile)
    {
        $ro->headers[self::X_FAKE_JSON] = $schemaFile;
        $ro->headers[self::X_JSON_SCHEMA_EXCEPTION] = $e->getMessage();
        $ro->body = $this->fakeResponse($schemaFile);
        $ro->view = null;
    }

    /** @return array<int|string, mixed> */
    private function fakeResponse(string $schemaFile): array
    {
        /** @var array<int|string, mixed> $fakeObject */
        $fakeObject = (new Faker())->generate(new SplFileInfo($schemaFile));

        return $this->deepArray($fakeObject);
    }

    /**
     * @param array<int|string, mixed> $values
     *
     * @return array<int|string, mixed>|stdClass
     */
    private function deepArray(array|stdClass $values): array
    {
        $result = [];
        /** @psalm-suppress MixedAssignment */
        foreach ($values as $key => $value) {
            $result[$key] = is_object($value) ? $this->deepArray((array) $value) : $result[$key] = $value;
        }

        return $result;
    }
}

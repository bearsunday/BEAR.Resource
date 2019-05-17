<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\JsonSchemaException;
use JSONSchemaFaker\Faker;

class JsonSchemaExceptionFakeHandler implements JsonSchemaExceptionHandlerInterface
{
    const X_FAKE_JSON = 'X-Fake-JSON';

    const X_JSON_SCHEMA_EXCEPTION = 'X-JSON-Schema-Exception';

    public function handle(ResourceObject $ro, JsonSchemaException $e, string $schemaFile)
    {
        $ro->headers[self::X_FAKE_JSON] = $schemaFile;
        $ro->headers[self::X_JSON_SCHEMA_EXCEPTION] = $e->getMessage();
        $ro->body = $this->fakeResponse($schemaFile);
        $ro->view = null;
    }

    private function fakeResponse(string $schemaFile) : array
    {
        $fakeObject = (new Faker)->generate(new \SplFileInfo($schemaFile));

        return $this->deepArray($fakeObject);
    }

    private function deepArray($values) : array
    {
        $result = [];
        foreach ($values as $key => $value) {
            $result[$key] = is_object($value) ? $this->deepArray((array) $value) : $result[$key] = $value;
        }

        return $result;
    }
}

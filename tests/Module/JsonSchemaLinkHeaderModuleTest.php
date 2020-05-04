<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Exception\InvalidSchemaUriException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class JsonSchemaLinkHeaderModuleTest extends TestCase
{
    public function testJsonSchemaLinkHeaderModule() : void
    {
        $jsonSchemaHost = 'http://example.com/schema/';
        $injector = new Injector(new JsonSchemaLinkHeaderModule($jsonSchemaHost));
        $instance = $injector->getInstance('', 'json_schema_host');
        $this->assertSame($jsonSchemaHost, $instance);
    }

    public function testInvalidSchema() : void
    {
        $this->expectException(InvalidSchemaUriException::class);

        $jsonSchemaHost = 'example.com';
        new Injector(new JsonSchemaLinkHeaderModule($jsonSchemaHost));
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaNotFoundException;
use BEAR\Resource\JsonSchema\FakePerson;
use BEAR\Resource\JsonSchema\FakeUser;
use BEAR\Resource\JsonSchema\FakeUsers;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use LogicException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class JsonSchemaModuleTest extends TestCase
{
    public function testValid() : void
    {
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        $ro->onGet(20);
        $this->assertSame($ro->body['name']['firstName'], 'mucha');
    }

    public function testValidArrayRef() : void
    {
        $ro = $this->getRo(FakeUsers::class);
        assert($ro instanceof FakeUsers);
        $ro->onGet(20);
        $this->assertSame($ro->body[0]['name']['firstName'], 'mucha');
    }

    public function testValidateException() : JsonSchemaException
    {
        $e = $this->createJsonSchemaException(FakeUser::class);
        $this->assertInstanceOf(JsonSchemaException::class, $e);

        return $e;
    }

    public function testBCValidateException() : JsonSchemaException
    {
        $e = $this->createJsonSchemaException(FakePerson::class);
        $this->assertInstanceOf(JsonSchemaException::class, $e);

        return $e;
    }

    /**
     * @depends testValidateException
     */
    public function testBCValidateErrorException(JsonSchemaException $e) : void
    {
        $expected = '[age] Must have a minimum value of 20';
        $this->assertStringContainsString($expected, $e->getMessage());
    }

    public function testException() : void
    {
        $this->expectException(JsonSchemaNotFoundException::class);
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        $ro->onPost();
    }

    public function testParameterException() : void
    {
        $caughtException = null;
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        try {
            $ro->onGet(30, 'invalid gender');
        } catch (JsonSchemaException $e) {
            $caughtException = $e;
        }
        $this->assertEmpty($ro->body);
        $this->assertInstanceOf(JsonSchemaException::class, $caughtException);
    }

    public function testWorksOnlyCode200() : void
    {
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        $ro->onPut();
        $this->assertInstanceOf(ResourceObject::class, $ro);
    }

    public function invalidRequestTest() : void
    {
        $this->expectException(JsonSchemaNotFoundException::class);
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        $ro->onPatch();
    }

    public function linkHeaderTest() : void
    {
        $ro = $this->getLinkHeaderRo(FakeUser::class);
        $this->assertSame('<http://example.com/schema/user.json>; rel="describedby"', $ro->headers['Link']);
    }

    private function createJsonSchemaException(string $class) : JsonSchemaException
    {
        $ro = $this->getRo($class);
        assert($ro instanceof ResourceObject);
        try {
            $ro->onGet(10); // @phpstan-ignore-line
        } catch (JsonSchemaException $e) {
            return $e;
        }

        throw new LogicException;
    }

    private function getRo(string $class) : ResourceObject
    {
        $module = $this->getJsonSchemaModule();
        $ro = (new Injector($module, __DIR__ . '/tmp'))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    private function getLinkHeaderRo(string $class) : ResourceObject
    {
        $jsonSchemaHost = 'http://example.com/schema/';
        $module = $this->getJsonSchemaModule();
        $module->install(new JsonSchemaLinkHeaderModule($jsonSchemaHost));
        $ro = (new Injector($module, __DIR__ . '/tmp'))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    private function getJsonSchemaModule() : JsonSchemaModule
    {
        $jsonSchema = dirname(__DIR__) . '/Fake/json_schema';
        $jsonValidate = dirname(__DIR__) . '/Fake/json_validate';

        return new JsonSchemaModule($jsonSchema, $jsonValidate);
    }
}

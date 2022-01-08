<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\Exception\JsonSchemaNotFoundException;
use BEAR\Resource\JsonSchema\FakeUser;
use BEAR\Resource\JsonSchema\FakeVoidUser;
use BEAR\Resource\JsonSchema\FakeVoidUsers;
use BEAR\Resource\JsonSchemaExceptionFakeHandler;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function dirname;

class JsonSchemaFakeModuleTest extends TestCase
{
    public function testValid(): void
    {
        $ro = $this->getRo(FakeVoidUser::class);
        assert($ro instanceof FakeVoidUser);
        $ro->onGet(20);
        $this->assertStringContainsString('user.json', $ro->headers[JsonSchemaExceptionFakeHandler::X_FAKE_JSON]);
    }

    public function testValidArrayRef(): void
    {
        $ro = $this->getRo(FakeVoidUsers::class);
        assert($ro instanceof FakeVoidUsers);
        $ro->onGet(20);
        $this->assertStringContainsString('users.json', $ro->headers[JsonSchemaExceptionFakeHandler::X_FAKE_JSON]);
        $this->assertIsString($ro->body[0]['name']['firstName']); // @phpstan-ignore-line
    }

    public function testException(): void
    {
        $this->expectException(JsonSchemaNotFoundException::class);
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        $ro->onPost();
    }

    public function testParameterException(): void
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

    public function testWorksOnlyCode200(): void
    {
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        $ro->onPut();
        $this->assertInstanceOf(ResourceObject::class, $ro);
    }

    public function invalidRequestTest(): void
    {
        $this->expectException(JsonSchemaNotFoundException::class);
        $ro = $this->getRo(FakeUser::class);
        assert($ro instanceof FakeUser);
        $ro->onPatch();
    }

    public function linkHeaderTest(): void
    {
        $ro = $this->getLinkHeaderRo(FakeUser::class);
        $this->assertSame('<http://example.com/schema/user.json>; rel="describedby"', $ro->headers['Link']);
    }

    /**
     * @param class-string $class
     */
    private function getRo(string $class): ResourceObject
    {
        $module = $this->getJsonSchemaModule();
        $ro = (new Injector($module, __DIR__ . '/tmp'))->getInstance($class);
        assert($ro instanceof ResourceObject);
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    /**
     * @param class-string $class
     */
    private function getLinkHeaderRo(string $class): FakeUser
    {
        $jsonSchemaHost = 'http://example.com/schema/';
        $module = $this->getJsonSchemaModule();
        $module->install(new JsonSchemaLinkHeaderModule($jsonSchemaHost));
        $ro = (new Injector($module, __DIR__ . '/tmp'))->getInstance($class);
        assert($ro instanceof FakeUser);
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    private function getJsonSchemaModule(): FakeJsonModule
    {
        $jsonSchema = dirname(__DIR__) . '/Fake/json_schema';
        $jsonValidate = dirname(__DIR__) . '/Fake/json_validate';

        return new FakeJsonModule(new JsonSchemaModule($jsonSchema, $jsonValidate));
    }
}

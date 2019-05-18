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

class JsonSchemaFakeModuleTest extends TestCase
{
    public function testValid()
    {
        $ro = $this->getRo(FakeVoidUser::class);
        $ro->onGet(20);
        $this->assertContains('user.json', $ro->headers[JsonSchemaExceptionFakeHandler::X_FAKE_JSON]);
    }

    public function testValidArrayRef()
    {
        $ro = $this->getRo(FakeVoidUsers::class);
        $ro->onGet(20);
        $this->assertContains('users.json', $ro->headers[JsonSchemaExceptionFakeHandler::X_FAKE_JSON]);
        $this->assertInternalType('string', $ro->body[0]['name']['firstName']);
    }

    public function testException()
    {
        $this->expectException(JsonSchemaNotFoundException::class);
        $ro = $this->getRo(FakeUser::class);
        $ro->onPost();
    }

    public function testParameterException()
    {
        $caughtException = null;
        $ro = $this->getRo(FakeUser::class);
        try {
            $ro->onGet(30, 'invalid gender');
        } catch (JsonSchemaException $e) {
            $caughtException = $e;
        }
        $this->assertEmpty($ro->body);
        $this->assertInstanceOf(JsonSchemaException::class, $caughtException);
    }

    public function testWorksOnlyCode200()
    {
        $ro = $this->getRo(FakeUser::class);
        $ro->onPut();
        $this->assertInstanceOf(ResourceObject::class, $ro);
    }

    public function invalidRequestTest()
    {
        $this->expectException(JsonSchemaNotFoundException::class);
        $ro = $this->getRo(FakeUser::class);
        $ro->onPatch();
    }

    public function linkHeaderTest()
    {
        $ro = $this->getLinkHeaderRo(FakeUser::class);
        $this->assertSame('<http://example.com/schema/user.json>; rel="describedby"', $ro->headers['Link']);
    }

    private function getRo(string $class)
    {
        $module = $this->getJsonSchemaModule();
        $ro = (new Injector($module, $_ENV['TMP_DIR']))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    private function getLinkHeaderRo(string $class)
    {
        $jsonSchemaHost = 'http://example.com/schema/';
        $module = $this->getJsonSchemaModule();
        $module->install(new JsonSchemaLinkHeaderModule($jsonSchemaHost));
        $ro = (new Injector($module, $_ENV['TMP_DIR']))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    private function getJsonSchemaModule() : FakeJsonModule
    {
        $jsonSchema = dirname(__DIR__) . '/Fake/json_schema';
        $jsonValidate = dirname(__DIR__) . '/Fake/json_validate';

        return new FakeJsonModule(new JsonSchemaModule($jsonSchema, $jsonValidate));
    }
}

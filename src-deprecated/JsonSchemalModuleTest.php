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
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

/**
 * @deprecated
 */
class JsonSchemalModuleTest extends TestCase
{
    public function testValid()
    {
        $ro = $this->getFakeUser();
        $ro->onGet(20);
        $this->assertSame($ro->body['name']['firstName'], 'mucha');
    }

    public function testValidArrayRef()
    {
        $ro = $this->getFakeUsers();
        $ro->onGet(20);
        $this->assertSame($ro->body[0]['name']['firstName'], 'mucha');
    }

    public function testValidateException()
    {
        $e = $this->createJsonSchemaException(FakeUser::class);
        $this->assertInstanceOf(JsonSchemaException::class, $e);

        return $e;
    }

    public function testBCValidateException()
    {
        $e = $this->createJsonSchemaException(FakePerson::class);
        $this->assertInstanceOf(JsonSchemaException::class, $e);

        return $e;
    }

    /**
     * @depends testValidateException
     */
    public function testBCValidateErrorException(JsonSchemaException $e)
    {
        $expected = '[age] Must have a minimum value of 20';
        $this->assertContains($expected, $e->getMessage());
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

    private function createJsonSchemaException($class)
    {
        $ro = $this->getRo($class);
        try {
            $ro->onGet(10);
        } catch (JsonSchemaException $e) {
            return $e;
        }
    }

    private function getFakeUser() : FakeUser
    {
        return $this->getRo(FakeUser::class);
    }

    private function getFakeUsers() : FakeUsers
    {
        return $this->getRo(FakeUsers::class);
    }

    private function getRo(string $class)
    {
        $jsonSchema = dirname(__DIR__) . '/Fake/json_schema';
        $jsonValidate = dirname(__DIR__) . '/Fake/json_validate';
        $ro = (new Injector(new JsonSchemalModule($jsonSchema, $jsonValidate), $_ENV['TMP_DIR']))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }
}

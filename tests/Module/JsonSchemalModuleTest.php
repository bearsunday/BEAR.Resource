<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\JsonSchema\FakePerson;
use BEAR\Resource\JsonSchema\FakeUser;
use BEAR\Resource\JsonSchema\FakeUsers;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class JsonSchemalModuleTest extends TestCase
{
    public function testValid()
    {
        $ro = $this->createRo(FakeUser::class);
        $ro->onGet(20);
        $this->assertSame($ro->body['name']['firstName'], 'mucha');
    }

    public function testValidArrayRef()
    {
        $ro = $this->createRo(FakeUsers::class);
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
        $errors = [];
        while ($e = $e->getPrevious()) {
            $errors[] = $e->getMessage();
        }
        $expected = ['[age] Must have a minimum value of 20'];
        $this->assertSame($expected, $errors);
    }

    /**
     * @expectException \BEAR\Resource\Exception\JsonSchemaNotFoundException
     */
    public function testException()
    {
        $ro = $this->createRo(FakeUser::class);
        $ro->onPost();
    }

    public function testWorksOnlyCode200()
    {
        $ro = $this->createRo(FakeUser::class);
        $ro->onPut();
        $this->assertInstanceOf(ResourceObject::class, $ro);
    }

    /**
     * @expectException \BEAR\Resource\Exception\JsonSchemaNotFoundException
     */
    public function invalidRequestTest()
    {
        $ro = $this->createRo(FakeUser::class);
        $ro->onPatch();
    }

    private function createJsonSchemaException($class)
    {
        $ro = $this->createRo($class);
        try {
            $ro->onGet(10);
        } catch (JsonSchemaException $e) {
            return $e;
        }
    }

    /**
     * @param $class
     *
     * @return FakeUser|mixed
     */
    private function createRo($class)
    {
        $jsonSchema = dirname(__DIR__) . '/Fake/json_schema';
        $jsonValidate = dirname(__DIR__) . '/Fake/json_validate';
        $ro = (new Injector(new JsonSchemalModule($jsonSchema, $jsonValidate), $_ENV['TMP_DIR']))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }
}

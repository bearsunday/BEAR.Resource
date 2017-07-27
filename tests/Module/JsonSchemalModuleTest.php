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
use BEAR\Resource\Uri;
use Ray\Di\Injector;

class JsonSchemalModuleTest extends \PHPUnit_Framework_TestCase
{
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
     * @expectedException \BEAR\Resource\Exception\JsonSchemaNotFoundException
     */
    public function testException()
    {
        $ro = $this->createRo(FakeUser::class);
        $ro->onPost();
    }

    private function createJsonSchemaException($class)
    {
        $ro = $this->createRo($class);
        try {
            $ro->onGet('1');
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
        $ro = (new Injector(new JsonSchemalModule($jsonSchema, $jsonValidate)))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }
}

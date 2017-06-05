<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Exception\JsonSchemaException;
use BEAR\Resource\JsonSchema\FakePerson;
use Ray\Di\Injector;

class JsonSchemalModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateException()
    {
        $e = $this->createJsonSchemaException();
        $this->assertInstanceOf(JsonSchemaException::class, $e);

        return $e;
    }

    /**
     * @depends testValidateException
     */
    public function testValidateErrorException(JsonSchemaException $e)
    {
        while ($e = $e->getPrevious()) {
            $errors[] = $e->getMessage();
        }
        $expected = ['[age] Must have a minimum value of 20'];
        $this->assertSame($expected, $errors);
    }

    private function createJsonSchemaException()
    {
        $person = (new Injector(new JsonSchemalModule))->getInstance(FakePerson::class);
        /* @var $person FakePerson */
        try {
            $person->onGet();
        } catch (JsonSchemaException $e) {
            return $e;
        }
    }
}

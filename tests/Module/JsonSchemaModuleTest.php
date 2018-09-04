<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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

class JsonSchemaModuleTest extends TestCase
{
    public function testValid()
    {
        $ro = $this->getRo(FakeUser::class);
        $ro->onGet(20);
        $this->assertSame($ro->body['name']['firstName'], 'mucha');
    }

    public function testValidArrayRef()
    {
        $ro = $this->getRo(FakeUsers::class);
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

    private function createJsonSchemaException($class)
    {
        $ro = $this->getRo($class);
        try {
            $ro->onGet(10);
        } catch (JsonSchemaException $e) {
            return $e;
        }
    }

    private function getRo(string $class)
    {
        $module = $this->getModule();
        $ro = (new Injector($module, $_ENV['TMP_DIR']))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    private function getLinkHeaderRo(string $class)
    {
        $jsonSchemaHost = 'http://example.com/schema/';
        $module = $this->getModule();
        $module->install(new JsonSchemaLinkHeaderModule($jsonSchemaHost));
        $ro = (new Injector($module, $_ENV['TMP_DIR']))->getInstance($class);
        /* @var $ro FakeUser */
        $ro->uri = new Uri('app://self/user?id=1');

        return $ro;
    }

    private function getModule() : JsonSchemaModule
    {
        $jsonSchema = dirname(__DIR__) . '/Fake/json_schema';
        $jsonValidate = dirname(__DIR__) . '/Fake/json_validate';
        $module = new JsonSchemaModule($jsonSchema, $jsonValidate);

        return $module;
    }
}

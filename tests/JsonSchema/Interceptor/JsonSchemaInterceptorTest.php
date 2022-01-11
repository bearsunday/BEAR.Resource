<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema\Interceptor;

use BEAR\Resource\Exception\JsonSchemaKeytNotFoundException;
use BEAR\Resource\Interceptor\JsonSchemaInterceptor;
use BEAR\Resource\JsonSchema\FakeUser;
use BEAR\Resource\JsonSchemaExceptionNullHandler;
use BEAR\Resource\ResourceObject;
use PHPUnit\Framework\TestCase;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\ReflectiveMethodInvocation;

use function dirname;

class JsonSchemaInterceptorTest extends TestCase
{
    private JsonSchemaInterceptor $jsonSchemaIntercetor;

    protected function setup(): void
    {
        $fakeDir = dirname(__DIR__, 2);
        $this->jsonSchemaIntercetor = new JsonSchemaInterceptor(
            $fakeDir . '/Fake/json_schema',
            $fakeDir . '/Fake/json_validate',
            new JsonSchemaExceptionNullHandler(),
            'http://example.com/schema/'
        );
    }

    public function testInvalidKeyJsonSchema(): void
    {
        $this->expectException(JsonSchemaKeytNotFoundException::class);
        $object = new FakeUser();
        /** @var array<MethodInterceptor> $interceptrs */
        $interceptrs = [JsonSchemaInterceptor::class];
        $invocation = new ReflectiveMethodInvocation($object, 'invalidKey', [], $interceptrs);
        $this->jsonSchemaIntercetor->invoke($invocation);
    }

    /**
     * covers \BEAR\Resource\Interceptor\JsonSchemaInterceptor::getTarget()
     */
    public function testTarget(): void
    {
        $object = new FakeUser();
        /** @var array<MethodInterceptor> $interceptrs */
        $interceptrs = [JsonSchemaInterceptor::class];
        $invocation = new ReflectiveMethodInvocation($object, 'bodyKey', [], $interceptrs);
        $ro = $this->jsonSchemaIntercetor->invoke($invocation);
        $this->assertInstanceOf(ResourceObject::class, $object);
    }

    public function testNotJsonView(): void
    {
        $object = new FakeUser();
        $object->view = 'string';
        /** @var array<MethodInterceptor> $interceptrs */
        $interceptrs = [JsonSchemaInterceptor::class];
        $invocation = new ReflectiveMethodInvocation($object, 'bodyKey', [], $interceptrs);
        $ro = $this->jsonSchemaIntercetor->invoke($invocation);
        $this->assertInstanceOf(ResourceObject::class, $object);
    }
}

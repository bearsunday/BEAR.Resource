<?php

declare(strict_types=1);

namespace BEAR\Resource\Options;

use BEAR\Resource\Fake\InputParam\FakeInputWithDefaultsResource;
use BEAR\Resource\Fake\InputParam\FakeNestedInputResource;
use BEAR\Resource\Fake\InputParam\FakeNoInputResource;
use BEAR\Resource\Fake\InputParam\FakeRequiredInputResource;
use BEAR\Resource\Fake\InputParam\FakeSingleInputResource;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class InputParamMetaTest extends TestCase
{
    private InputParamMeta $inputParamMeta;

    protected function setUp(): void
    {
        $this->inputParamMeta = new InputParamMeta();
    }

    public function testNoInputParameters(): void
    {
        $method = new ReflectionMethod(FakeNoInputResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertSame([], $result);
    }

    public function testSingleInputParameter(): void
    {
        $method = new ReflectionMethod(FakeSingleInputResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertTrue(isset($result['parameters']));
        $this->assertIsArray($result['parameters']);
        $this->assertArrayHasKey('name', $result['parameters']);
        $this->assertArrayHasKey('age', $result['parameters']);

        $this->assertIsArray($result['parameters']['name']);
        $this->assertIsArray($result['parameters']['age']);
        $this->assertSame('string', $result['parameters']['name']['type']);
        $this->assertSame('integer', $result['parameters']['age']['type']);
        $this->assertSame('user', $result['parameters']['name']['group']);
        $this->assertSame('user', $result['parameters']['age']['group']);
    }

    public function testInputParameterWithDefaults(): void
    {
        $method = new ReflectionMethod(FakeInputWithDefaultsResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertTrue(isset($result['parameters']));
        $this->assertIsArray($result['parameters']);
        $this->assertArrayHasKey('page', $result['parameters']);
        $this->assertArrayHasKey('limit', $result['parameters']);

        $this->assertIsArray($result['parameters']['page']);
        $this->assertIsArray($result['parameters']['limit']);
        $this->assertSame('1', $result['parameters']['page']['default']);
        $this->assertSame('20', $result['parameters']['limit']['default']);

        $this->assertArrayNotHasKey('required', $result);
    }

    public function testNestedInputParameters(): void
    {
        $method = new ReflectionMethod(FakeNestedInputResource::class, 'onPost');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertTrue(isset($result['parameters']));
        $this->assertIsArray($result['parameters']);
        // Parent level parameters
        $this->assertArrayHasKey('name', $result['parameters']);
        $this->assertArrayHasKey('age', $result['parameters']);
        // Nested parameters
        $this->assertArrayHasKey('city', $result['parameters']);
        $this->assertArrayHasKey('street', $result['parameters']);

        $this->assertIsArray($result['parameters']['name']);
        $this->assertIsArray($result['parameters']['city']);
        $this->assertSame('user', $result['parameters']['name']['group']);
        $this->assertSame('address', $result['parameters']['city']['group']);
    }

    public function testRequiredParameters(): void
    {
        $method = new ReflectionMethod(FakeRequiredInputResource::class, 'onGet');
        $result = $this->inputParamMeta->get($method);

        $this->assertArrayHasKey('required', $result);
        $this->assertTrue(isset($result['required']));
        $this->assertIsArray($result['required']);
        $this->assertContains('query', $result['required']);
        $this->assertNotContains('category', $result['required']);
    }
}

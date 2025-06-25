<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Exception\ParameterInvalidEnumException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class InputParamCoverageTest extends TestCase
{
    private NamedParameter $namedParameter;
    private TestResourceWithoutConstructor $testResourceWithoutConstructor;
    private TestResourceEnum $testResourceEnum;
    private TestResourceWithDefaults $testResourceWithDefaults;
    private TestResourceUnresolvable $testResourceUnresolvable;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $this->testResourceWithoutConstructor = new TestResourceWithoutConstructor();
        $this->testResourceEnum = new TestResourceEnum();
        $this->testResourceWithDefaults = new TestResourceWithDefaults();
        $this->testResourceUnresolvable = new TestResourceUnresolvable();
    }

    public function testClassWithoutConstructor(): void
    {
        $params = [
            'person' => [
                'name' => 'John',
                'age' => 30,
            ],
        ];

        $args = $this->namedParameter->getParameters([$this->testResourceWithoutConstructor, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $this->testResourceWithoutConstructor->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('John', $result->body['name']);
        $this->assertSame(30, $result->body['age']);
    }

    public function testEnumParam(): void
    {
        $params = ['status' => 'foo'];

        $args = $this->namedParameter->getParameters([$this->testResourceEnum, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $this->testResourceEnum->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('foo', $result->body['status']);
    }

    public function testEnumInvalidValue(): void
    {
        $this->expectException(ParameterInvalidEnumException::class);

        $params = ['status' => 'invalid'];

        $args = $this->namedParameter->getParameters([$this->testResourceEnum, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $this->testResourceEnum->onGet(...array_values($args));
    }

    public function testDefaultValues(): void
    {
        $params = [
            'person' => ['name' => 'John'],
        ];

        $args = $this->namedParameter->getParameters([$this->testResourceWithDefaults, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $this->testResourceWithDefaults->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('John', $result->body['name']);
        $this->assertSame(25, $result->body['age']);
    }

    public function testUnresolvableParameter(): void
    {
        $this->expectException(ParameterException::class);

        $params = [];

        $args = $this->namedParameter->getParameters([$this->testResourceUnresolvable, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $this->testResourceUnresolvable->onGet(...array_values($args));
    }

    public function testStructuredDataMissingKey(): void
    {
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage("Required key 'user' not found");

        $params = [];

        $args = $this->namedParameter->getParameters([$this->testResourceWithDefaults, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $this->testResourceWithDefaults->onPost(...array_values($args));
    }

    public function testStructuredDataInvalidType(): void
    {
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage("Data under key 'user' must be an array");

        $params = ['user' => 'not an array'];

        $args = $this->namedParameter->getParameters([$this->testResourceWithDefaults, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $this->testResourceWithDefaults->onPost(...array_values($args));
    }
}

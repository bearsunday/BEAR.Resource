<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\BoolValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\FooValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\IntValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\NamedIntValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\QualifiedIntValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\ScalarModule;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\ScalarValueObject;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\ServiceInterface;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\StringValue;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;

class ScalarParamTest extends TestCase
{
    public function testScalarToValueObject(): void
    {
        // Arrange
        $injector = new Injector(new ScalarModule());
        $namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $vo = new ScalarValueObject();
        $params = [
            'int' => 123,
            'string' => 'hello',
            'bool' => true,
            'foo' => ['value' => 'bar'],
            'qualifiedInt' => 456,
            'namedInt' => 789,
        ];

        // Act
        $args = $namedParameter->getParameters([$vo, 'onGet'], $params);
        $ro = $vo->onGet(...array_values($args)); // @phpstan-ignore-line

        // Assert
        $this->assertInstanceOf(ResourceObject::class, $ro);
        $this->assertSame(200, $ro->code);
        $this->assertSame([
            'int' => 123,
            'string' => 'hello',
            'bool' => true,
            'foo' => ['value' => 'bar'],
            'qualifiedInt' => 456,
            'service' => 'service implementation',
            'qualifiedService' => 'other service implementation',
        ], $ro->body);
    }

    public function testValueObjectTypes(): void
    {
        // Arrange
        $injector = new Injector(new ScalarModule());
        $namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $vo = new ScalarValueObject();
        $params = [
            'int' => 123,
            'string' => 'hello',
            'bool' => true,
            'foo' => ['value' => 'bar'],
            'qualifiedInt' => 456,
            'namedInt' => 789,
        ];

        // Act
        $args = $namedParameter->getParameters([$vo, 'onGet'], $params);

        // Assert
        $this->assertInstanceOf(IntValue::class, $args['int']);
        $this->assertSame(123, $args['int']->value);

        $this->assertInstanceOf(StringValue::class, $args['string']);
        $this->assertSame('hello', $args['string']->value);

        $this->assertInstanceOf(BoolValue::class, $args['bool']);
        $this->assertSame(true, $args['bool']->value);

        $this->assertInstanceOf(FooValue::class, $args['foo']);
        $this->assertSame(['value' => 'bar'], $args['foo']->value);

        $this->assertInstanceOf(QualifiedIntValue::class, $args['qualifiedInt']);
        $this->assertSame(456, $args['qualifiedInt']->value);
    }

    public function testScalarWithDependencyInjection(): void
    {
        // Arrange
        $injector = new Injector(new ScalarModule());
        $namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $vo = new ScalarValueObject();
        $params = [
            'int' => 123,
            'string' => 'hello',
            'bool' => true,
            'foo' => ['value' => 'bar'],
            'qualifiedInt' => 456,
            'namedInt' => 789,
        ];

        // Act
        $args = $namedParameter->getParameters([$vo, 'onGet'], $params);

        // Assert
        // Check regular dependency injection
        $this->assertInstanceOf(IntValue::class, $args['int']);
        $this->assertSame(123, $args['int']->value);
        $this->assertInstanceOf(ServiceInterface::class, $args['int']->service);
        $this->assertSame('service implementation', $args['int']->service->serve());

        // Check qualified dependency injection
        $this->assertInstanceOf(QualifiedIntValue::class, $args['qualifiedInt']);
        $this->assertSame(456, $args['qualifiedInt']->value);
        $this->assertInstanceOf(ServiceInterface::class, $args['qualifiedInt']->service);
        $this->assertSame('other service implementation', $args['qualifiedInt']->service->serve());

        // Check named dependency injection
        $this->assertInstanceOf(NamedIntValue::class, $args['namedInt']);
    }
}

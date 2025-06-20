<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\BoolValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\FooValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\IntValue;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\ScalarValueObject;
use BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar\StringValue;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;

class SclarParam extends TestCase
{
    public function testScalarToValueObject(): void
    {
        // Arrange
        $namedParameter = new NamedParameter(new NamedParamMetas(), new Injector());
        $vo = new ScalarValueObject();
        $params = [
            'int' => 123,
            'string' => 'hello',
            'bool' => true,
            'foo' => ['value' => 'bar'],
        ];

        // Act
        $args = $namedParameter->getParameters([$vo, 'onGet'], $params);
        $ro = $vo->onGet(...array_values($args));

        // Assert
        $this->assertInstanceOf(ResourceObject::class, $ro);
        $this->assertSame(200, $ro->code);
        $this->assertSame([
            'int' => 123,
            'string' => 'hello',
            'bool' => true,
            'foo' => ['value' => 'bar'],
        ], $ro->body);
    }

    public function testValueObjectTypes(): void
    {
        // Arrange
        $namedParameter = new NamedParameter(new NamedParamMetas(), new Injector());
        $vo = new ScalarValueObject();
        $params = [
            'int' => 123,
            'string' => 'hello',
            'bool' => true,
            'foo' => ['value' => 'bar'],
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
    }
}

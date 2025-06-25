<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use FakeVendor\Sandbox\Resource\App\Class\NoConstructorObject;
use FakeVendor\Sandbox\Resource\App\Class\RequiredTestObject;
use FakeVendor\Sandbox\Resource\App\Class\SimpleTestObject;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class InputParamFullCoverageTest extends TestCase
{
    private NamedParameter $namedParameter;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
    }

    public function testCreateFromStructuredDataLegacyMode(): void
    {
        // Test legacy mode (no #[Input] attribute) - uses createFromStructuredData
        $testResource = new class extends ResourceObject {
            public function onGet(SimpleTestObject $user): static
            {
                $this->body = [
                    'name' => $user->name,
                    'age' => $user->age,
                ];

                return $this;
            }
        };

        $params = [
            'user' => [
                'name' => 'Alice',
                'age' => 28,
            ],
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $testResource->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('Alice', $result->body['name']);
        $this->assertSame(28, $result->body['age']);
    }

    public function testCreateEnumWithSnakeCase(): void
    {
        // Test enum creation with snake_case conversion
        $testResource = new class extends ResourceObject {
            public function onGet(FakeStringBacked $myEnum): static
            {
                $this->body = ['enum_value' => $myEnum->value];

                return $this;
            }
        };

        $params = [
            'my_enum' => 'foo', // snake_case parameter name
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $testResource->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('foo', $result->body['enum_value']);
    }

    public function testCreateWithoutConstructor(): void
    {
        // Test object creation without constructor
        $testResource = new class extends ResourceObject {
            public function onGet(NoConstructorObject $obj): static
            {
                $this->body = [
                    'name' => $obj->name,
                    'age' => $obj->age,
                ];

                return $this;
            }
        };

        $params = [
            'obj' => [
                'name' => 'Bob',
                'age' => 35,
            ],
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $testResource->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('Bob', $result->body['name']);
        $this->assertSame(35, $result->body['age']);
    }

    public function testGetPropsForClassParamWithSnakeCase(): void
    {
        // Test getPropsForClassParam method with snake_case conversion
        $testResource = new class extends ResourceObject {
            public function onGet(FakeStringBacked $myTestParam): static
            {
                $this->body = ['value' => $myTestParam->value];

                return $this;
            }
        };

        $params = [
            'my_test_param' => 'bar', // snake_case version of myTestParam
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $testResource->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('bar', $result->body['value']);
    }

    public function testCreateFromStructuredDataMissingParameter(): void
    {
        // Test missing required parameter in structured data
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage("Required parameter 'age' not found in key 'user'");

        $testResource = new class extends ResourceObject {
            public function onGet(RequiredTestObject $user): static
            {
                return $this;
            }
        };

        $params = [
            'user' => [
                'name' => 'Charlie', // missing 'age'
            ],
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $testResource->onGet(...array_values($args));
    }

    public function testCreateWithoutConstructorInvalidData(): void
    {
        // Test createWithoutConstructor with invalid data
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage('Expected array data');

        $testResource = new class extends ResourceObject {
            public function onGet(NoConstructorObject $obj): static
            {
                return $this;
            }
        };

        $params = ['obj' => 'invalid_string_data'];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $testResource->onGet(...array_values($args));
    }
}

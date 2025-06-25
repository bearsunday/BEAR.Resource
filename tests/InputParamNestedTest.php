<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Input;
use FakeVendor\Sandbox\Resource\App\Class\NestedInputTestObject;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class InputParamNestedTest extends TestCase
{
    private NamedParameter $namedParameter;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
    }

    public function testNestedInputInConstructor(): void
    {
        // Test nested #[Input] in constructor with structured data
        $testResource = new class extends ResourceObject {
            public function onGet(#[Input(key: 'data')]
            NestedInputTestObject $wrapper,): static
            {
                $this->body = [
                    'title' => $wrapper->title,
                    'nested_name' => $wrapper->nested->name,
                    'nested_age' => $wrapper->nested->age,
                ];

                return $this;
            }
        };

        $params = [
            'data' => [
                'title' => 'Main Title',
                'name' => 'Nested Name',    // These will be used for nested #[Input]
                'age' => 42,                 // These will be used for nested #[Input]
            ],
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $testResource->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('Main Title', $result->body['title']);
        $this->assertSame('Nested Name', $result->body['nested_name']);
        $this->assertSame(42, $result->body['nested_age']);
    }

    public function testNestedInputInFlatMapping(): void
    {
        // Test nested #[Input] with flat mapping mode
        $testResource = new class extends ResourceObject {
            public function onGet(#[Input]
            NestedInputTestObject $obj,): static
            {
                $this->body = [
                    'main' => $obj->title,
                    'nested_name' => $obj->nested->name,
                    'nested_age' => $obj->nested->age,
                ];

                return $this;
            }
        };

        $params = [
            'title' => 'Top Level',
            'name' => 'Inner Name',     // For nested #[Input]
            'age' => 33,                // For nested #[Input]
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $testResource->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertSame('Top Level', $result->body['main']);
        $this->assertSame('Inner Name', $result->body['nested_name']);
        $this->assertSame(33, $result->body['nested_age']);
    }
}

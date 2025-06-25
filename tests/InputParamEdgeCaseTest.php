<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\Exception\ParameterException;
use FakeVendor\Sandbox\Resource\App\Class\NoConstructorObject;
use FakeVendor\Sandbox\Resource\App\Class\SimpleTestObject;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;

class InputParamEdgeCaseTest extends TestCase
{
    private NamedParameter $namedParameter;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
    }

    public function testGetPropsForClassParamMissingParam(): void
    {
        // Test getPropsForClassParam when parameter is missing entirely
        $this->expectException(ParameterException::class);

        $testResource = new class extends ResourceObject {
            public function onGet(NoConstructorObject $missingParam): static
            {
                return $this;
            }
        };

        $params = []; // No parameter provided

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        $testResource->onGet(...array_values($args));
    }

    public function testCreateFromStructuredDataKeyWithInput(): void
    {
        // Test createFromStructuredData with #[Input(key: 'data')] for constructor object
        $testResource = new class extends ResourceObject {
            public function onGet(#[Input(key: 'data')]
            SimpleTestObject $obj,): static
            {
                $this->body = [
                    'name' => $obj->name,
                    'age' => $obj->age,
                ];

                return $this;
            }
        };

        $params = [
            'data' => [
                'name' => 'Dave',
                'age' => 40,
            ],
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        $result = $testResource->onGet(...array_values($args));

        $this->assertSame('Dave', $result->body['name']);
        $this->assertSame(40, $result->body['age']);
    }

    public function testInputAttributeWithNoAttributes(): void
    {
        // Test getInputAttribute when no attributes are present (returns null)
        // This is covered by legacy mode tests but ensures the method is hit
        $testResource = new class extends ResourceObject {
            public function onGet(SimpleTestObject $obj): static
            {
                $this->body = ['name' => $obj->name];

                return $this;
            }
        };

        $params = [
            'obj' => ['name' => 'Test', 'age' => 25],
        ];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        $result = $testResource->onGet(...array_values($args));

        $this->assertSame('Test', $result->body['name']);
    }
}

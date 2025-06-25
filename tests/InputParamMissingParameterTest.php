<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Input;
use BEAR\Resource\Exception\ParameterException;
use FakeVendor\Sandbox\Resource\App\Class\RequiredTestObject;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;

class InputParamMissingParameterTest extends TestCase
{
    private NamedParameter $namedParameter;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
    }

    public function testMissingRequiredParameterFlatMapping(): void
    {
        // Test missing required parameter with #[Input] attribute (flat mapping)
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage("Required parameter 'age' not found for FakeVendor\Sandbox\Resource\App\Class\RequiredTestObject");

        $testResource = new class extends ResourceObject {
            public function onGet(#[Input]
            RequiredTestObject $obj,): static
            {
                return $this;
            }
        };

        $params = ['name' => 'John']; // missing 'age' parameter

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $testResource->onGet(...array_values($args));
    }

    public function testMissingAllRequiredParametersFlatMapping(): void
    {
        // Test missing all required parameters with #[Input] attribute
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage("Required parameter 'name' not found for FakeVendor\Sandbox\Resource\App\Class\RequiredTestObject");

        $testResource = new class extends ResourceObject {
            public function onGet(#[Input]
            RequiredTestObject $obj,): static
            {
                return $this;
            }
        };

        $params = []; // no parameters at all

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $testResource->onGet(...array_values($args));
    }

    public function testMissingParameterAfterKebabCaseSearch(): void
    {
        // Test missing parameter - the first missing param (name) will trigger the exception
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage("Required parameter 'name' not found for FakeVendor\Sandbox\Resource\App\Class\RequiredTestObject");

        $testResource = new class extends ResourceObject {
            public function onGet(#[Input]
            RequiredTestObject $obj,): static
            {
                return $this;
            }
        };

        $params = ['other-param' => 'value']; // doesn't match 'name' or 'age

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $testResource->onGet(...array_values($args));
    }
}

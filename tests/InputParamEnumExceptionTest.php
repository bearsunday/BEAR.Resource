<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class InputParamEnumExceptionTest extends TestCase
{
    private NamedParameter $namedParameter;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
    }

    public function testEnumMissingParameterThrowsException(): void
    {
        // Test createEnum when getPropsForClassParam throws ParameterException
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage('missingEnum');

        $testResource = new class extends ResourceObject {
            public function onGet(FakeStringBacked $missingEnum): static
            {
                return $this;
            }
        };

        $params = []; // No parameters, so getPropsForClassParam will throw

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $testResource->onGet(...array_values($args));
    }

    public function testEnumMissingParameterWithDefault(): void
    {
        // Test createEnum when parameter is missing but has default value
        $testResource = new class extends ResourceObject {
            public function onGet(FakeStringBacked|null $optionalEnum = null): static
            {
                $this->body = ['enum' => $optionalEnum];

                return $this;
            }
        };

        $params = []; // No parameters, but default value exists

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $result = $testResource->onGet(...array_values($args));
        assert(is_array($result->body));

        $this->assertNull($result->body['enum']);
    }

    public function testEnumWithSnakeCaseMissing(): void
    {
        // Test enum parameter that's missing even with snake_case conversion
        $this->expectException(ParameterException::class);
        $this->expectExceptionMessage('enumParam');

        $testResource = new class extends ResourceObject {
            public function onGet(FakeStringBacked $enumParam): static
            {
                return $this;
            }
        };

        $params = ['other_param' => 'foo']; // Not matching enumParam or enum_param];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $testResource->onGet(...array_values($args));
    }
}

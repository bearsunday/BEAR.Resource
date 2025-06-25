<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Input;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;

class InputParamOptionalTest extends TestCase
{
    private NamedParameter $namedParameter;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
    }

    public function testNullableParameterWithoutValue(): void
    {
        $testResource = new class extends ResourceObject {
            public function onGet(#[Input]
            string|null $optionalParam = null,): static
            {
                $this->body = ['param' => $optionalParam];

                return $this;
            }
        };

        $params = []; // No parameter provided

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        $result = $testResource->onGet(...array_values($args));

        $this->assertNull($result->body['param']);
    }

    public function testNullableParameterWithValue(): void
    {
        $testResource = new class extends ResourceObject {
            public function onGet(#[Input]
            string|null $optionalParam = null,): static
            {
                $this->body = ['param' => $optionalParam];

                return $this;
            }
        };

        $params = ['optionalParam' => 'test'];

        $args = $this->namedParameter->getParameters([$testResource, 'onGet'], $params);
        $result = $testResource->onGet(...array_values($args));

        $this->assertSame('test', $result->body['param']);
    }
}

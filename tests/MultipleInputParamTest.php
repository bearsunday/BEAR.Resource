<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\MultipleInputTest;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class MultipleInputParamTest extends TestCase
{
    private NamedParameter $namedParameter;
    private MultipleInputTest $multipleInputTest;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $this->multipleInputTest = new MultipleInputTest();
    }

    public function testMultipleInputParams(): void
    {
        $params = [
            'query' => 'laptop',
            'category' => 'electronics',
            'page' => '2',
            'limit' => '10',
        ];

        $args = $this->namedParameter->getParameters([$this->multipleInputTest, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->multipleInputTest->onGet(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('laptop', $ro->body['search']['query']);
        $this->assertSame('electronics', $ro->body['search']['category']);
        $this->assertSame(2, $ro->body['pagination']['page']);
        $this->assertSame(10, $ro->body['pagination']['limit']);
        $this->assertSame(10, $ro->body['pagination']['offset']); // (2-1) * 10
    }

    public function testWithDefaults(): void
    {
        $params = ['query' => 'smartphone'];

        $args = $this->namedParameter->getParameters([$this->multipleInputTest, 'onGet'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->multipleInputTest->onGet(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('smartphone', $ro->body['search']['query']);
        $this->assertNull($ro->body['search']['category']);
        $this->assertSame(1, $ro->body['pagination']['page']); // default
        $this->assertSame(20, $ro->body['pagination']['limit']); // default
        $this->assertSame(0, $ro->body['pagination']['offset']); // (1-1) * 20
    }
}

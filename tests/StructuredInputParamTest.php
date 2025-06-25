<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\StructuredInputTest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class StructuredInputParamTest extends TestCase
{
    private NamedParameter $namedParameter;
    private StructuredInputTest $structuredInputTest;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $this->structuredInputTest = new StructuredInputTest();
    }

    public function testStructuredInputWithKey(): void
    {
        // Test structured data like ClassParam (user[name]=John&user[age]=30&user[email]=john@example.com)
        $params = [
            'user' => [
                'name' => 'John Doe',
                'age' => 30,
                'email' => 'john@example.com',
            ],
        ];

        $args = $this->namedParameter->getParameters([$this->structuredInputTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->structuredInputTest->onPost(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('John Doe', $ro->body['name']);
        $this->assertSame(30, $ro->body['age']);
        $this->assertSame('john@example.com', $ro->body['email']);
    }

    public function testMultipleStructuredInputs(): void
    {
        // Test multiple structured inputs with different keys
        $params = [
            'user' => [
                'name' => 'Alice',
                'age' => 25,
                'email' => 'alice@example.com',
            ],
            'admin' => [
                'name' => 'Bob Admin',
                'age' => 35,
                'email' => 'bob@admin.com',
            ],
        ];

        $args = $this->namedParameter->getParameters([$this->structuredInputTest, 'onPut'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->structuredInputTest->onPut(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('Alice', $ro->body['user']['name']);
        $this->assertSame(25, $ro->body['user']['age']);
        $this->assertSame('alice@example.com', $ro->body['user']['email']);
        $this->assertSame('Bob Admin', $ro->body['admin']['name']);
        $this->assertSame(35, $ro->body['admin']['age']);
        $this->assertSame('bob@admin.com', $ro->body['admin']['email']);
    }

    public function testStructuredInputValidationError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        $params = [
            'user' => [
                'name' => 'John',
                'age' => 30,
                'email' => 'invalid-email',
            ],
        ];

        $args = $this->namedParameter->getParameters([$this->structuredInputTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $this->structuredInputTest->onPost(...array_values($args));
    }

    public function testMissingStructuredKey(): void
    {
        $this->expectException(Exception\ParameterException::class);
        $this->expectExceptionMessage("Required key 'user' not found");

        $params = [
            'other' => [
                'name' => 'John',
                'age' => 30,
            ],
        ];

        $args = $this->namedParameter->getParameters([$this->structuredInputTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $this->structuredInputTest->onPost(...array_values($args));
    }
}

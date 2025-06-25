<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\LegacyTest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class LegacyInputParamTest extends TestCase
{
    private NamedParameter $namedParameter;
    private LegacyTest $legacyTest;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $this->legacyTest = new LegacyTest();
    }

    public function testLegacyClassParamBehavior(): void
    {
        // Test ClassParam-style behavior for classes without #[Input] attribute
        // Expected format: user[name]=John&user[age]=30&user[email]=john@example.com
        $params = [
            'user' => [
                'name' => 'John Doe',
                'age' => 30,
                'email' => 'john@example.com',
            ],
        ];

        $args = $this->namedParameter->getParameters([$this->legacyTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->legacyTest->onPost(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('John Doe', $ro->body['name']);
        $this->assertSame(30, $ro->body['age']);
        $this->assertSame('john@example.com', $ro->body['email']);
    }

    public function testMultipleLegacyParameters(): void
    {
        // Test multiple ClassParam-style parameters
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

        $args = $this->namedParameter->getParameters([$this->legacyTest, 'onPut'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->legacyTest->onPut(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('Alice', $ro->body['user']['name']);
        $this->assertSame(25, $ro->body['user']['age']);
        $this->assertSame('alice@example.com', $ro->body['user']['email']);
        $this->assertSame('Bob Admin', $ro->body['admin']['name']);
        $this->assertSame(35, $ro->body['admin']['age']);
        $this->assertSame('bob@admin.com', $ro->body['admin']['email']);
    }

    public function testLegacyValidationError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Age must be positive');

        $params = [
            'user' => [
                'name' => 'John',
                'age' => -5,
                'email' => 'john@example.com',
            ],
        ];

        $args = $this->namedParameter->getParameters([$this->legacyTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $this->legacyTest->onPost(...array_values($args));
    }
}

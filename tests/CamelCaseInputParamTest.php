<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\CamelCaseTest;
use FakeVendor\Sandbox\Resource\App\NestedCamelCaseTest;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;
use function assert;
use function is_array;

class CamelCaseInputParamTest extends TestCase
{
    private NamedParameter $namedParameter;
    private CamelCaseTest $camelCaseTest;
    private NestedCamelCaseTest $nestedCamelCaseTest;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $this->camelCaseTest = new CamelCaseTest();
        $this->nestedCamelCaseTest = new NestedCamelCaseTest();
    }

    public function testKebabCaseToCamelCase(): void
    {
        // Test kebab-case HTTP parameters mapping to camelCase properties
        $params = [
            'first-name' => 'John',
            'last-name' => 'Doe',
            'full-name' => 'John Doe',
            'email-address' => 'john@example.com',
            'phone-number' => '1234567890',
        ];

        $args = $this->namedParameter->getParameters([$this->camelCaseTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->camelCaseTest->onPost(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('John', $ro->body['firstName']);
        $this->assertSame('Doe', $ro->body['lastName']);
        $this->assertSame('John Doe', $ro->body['fullName']);
        $this->assertSame('john@example.com', $ro->body['emailAddress']);
        $this->assertSame(1234567890, $ro->body['phoneNumber']);
    }

    public function testExactMatchTakesPrecedence(): void
    {
        // Test that exact camelCase match takes precedence over kebab-case
        $params = [
            'firstName' => 'Exact',
            'first-name' => 'Kebab',
            'lastName' => 'Match',
            'full-name' => 'Full Name',
            'email-address' => 'test@example.com',
            'phone-number' => '1111111111',
        ];

        $args = $this->namedParameter->getParameters([$this->camelCaseTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->camelCaseTest->onPost(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('Exact', $ro->body['firstName']); // exact match wins
        $this->assertSame('Match', $ro->body['lastName']); // exact match wins
        $this->assertSame('Full Name', $ro->body['fullName']); // kebab-case used
        $this->assertSame('test@example.com', $ro->body['emailAddress']); // kebab-case used
        $this->assertSame(1111111111, $ro->body['phoneNumber']); // kebab-case used
    }

    public function testNestedObjectWithKebabCase(): void
    {
        // Test nested objects with kebab-case parameters
        $params = [
            'first-name' => 'Bob',
            'last-name' => 'Wilson',
            'street-name' => 'Oak Avenue',
            'city-name' => 'Los Angeles',
            'zip-code' => '90210',
        ];

        $args = $this->namedParameter->getParameters([$this->nestedCamelCaseTest, 'onPost'], $params);
        /** @phpstan-ignore-next-line Parameter type mismatch, runtime provides correct types */
        $ro = $this->nestedCamelCaseTest->onPost(...array_values($args));
        assert(is_array($ro->body));

        $this->assertSame(200, $ro->code);
        $this->assertSame('Bob', $ro->body['user']['firstName']);
        $this->assertSame('Wilson', $ro->body['user']['lastName']);
        $this->assertSame('Oak Avenue', $ro->body['address']['streetName']);
        $this->assertSame('Los Angeles', $ro->body['address']['cityName']);
        $this->assertSame('90210', $ro->body['address']['zipCode']);
    }
}

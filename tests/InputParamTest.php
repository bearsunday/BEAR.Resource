<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\InputTest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function array_values;

class InputParamTest extends TestCase
{
    private NamedParameter $namedParameter;
    private InputTest $inputTest;

    protected function setUp(): void
    {
        $injector = new Injector();
        $this->namedParameter = new NamedParameter(new NamedParamMetas(), $injector);
        $this->inputTest = new InputTest();
    }

    public function testSimpleInputParam(): void
    {
        $params = [
            'firstName' => '田中',
            'lastName' => '太郎',
            'age' => '30',
            'email' => 'tanaka@example.com',
        ];

        $args = $this->namedParameter->getParameters([$this->inputTest, 'onPost'], $params);
        $ro = $this->inputTest->onPost(...array_values($args));

        $this->assertSame(200, $ro->code);
        $this->assertSame('田中 太郎', $ro->body['name']);
        $this->assertSame(30, $ro->body['age']);
        $this->assertSame('tanaka@example.com', $ro->body['email']);
    }

    public function testNestedInputParam(): void
    {
        $params = [
            'firstName' => '佐藤',
            'lastName' => '花子',
            'age' => '25',
            'prefecture' => '東京都',
            'city' => '新宿区',
            'street' => '西新宿1-1-1',
            'zipCode' => '160-0023',
        ];

        $args = $this->namedParameter->getParameters([$this->inputTest, 'onPut'], $params);
        $ro = $this->inputTest->onPut(...array_values($args));

        $this->assertSame(200, $ro->code);
        $this->assertSame('佐藤 花子', $ro->body['user']['name']);
        $this->assertSame(25, $ro->body['user']['age']);
        $this->assertSame('東京都', $ro->body['address']['prefecture']);
        $this->assertSame('新宿区', $ro->body['address']['city']);
        $this->assertSame('西新宿1-1-1', $ro->body['address']['street']);
        $this->assertSame('160-0023', $ro->body['address']['zipCode']);
    }

    public function testValidationError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('First name must be at least 2 characters');

        $params = [
            'firstName' => 'A',
            'lastName' => '田中',
            'age' => '30',
            'email' => 'a@example.com',
        ];

        $args = $this->namedParameter->getParameters([$this->inputTest, 'onPost'], $params);
        $this->inputTest->onPost(...array_values($args));
    }

    public function testEmailValidationError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email format');

        $params = [
            'firstName' => '田中',
            'lastName' => '太郎',
            'age' => '30',
            'email' => 'invalid-email',
        ];

        $args = $this->namedParameter->getParameters([$this->inputTest, 'onPost'], $params);
        $this->inputTest->onPost(...array_values($args));
    }

    public function testZipCodeValidationError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Zip code must be in 000-0000 format');

        $params = [
            'firstName' => '佐藤',
            'lastName' => '花子',
            'age' => '25',
            'prefecture' => '東京都',
            'city' => '新宿区',
            'street' => '西新宿1-1-1',
            'zipCode' => '1600023',  // invalid format
        ];

        $args = $this->namedParameter->getParameters([$this->inputTest, 'onPut'], $params);
        $this->inputTest->onPut(...array_values($args));
    }
}

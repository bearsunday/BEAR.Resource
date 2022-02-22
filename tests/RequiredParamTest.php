<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class RequiredParamTest extends TestCase
{
    public function testInvoke(): void
    {
        $injector = new Injector();
        $param = new RequiredParam();
        $query = ['var_name' => 1, 'nullValue' => null];

        $result = ($param)('varName', $query, $injector);
        $this->assertSame(1, $result);

        $result = ($param)('nullValue', $query, $injector);
        $this->assertNull($result);

        $this->expectException(ParameterException::class);
        ($param)('undefinedValue', $query, $injector);
    }
}

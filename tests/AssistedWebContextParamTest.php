<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\WebContextParam\Annotation\CookieParam;

class AssistedWebContextParamTest extends TestCase
{
    public function testAssistedWebContextParam()
    {
        $cookieParam = new CookieParam;
        $cookieParam->key = 'cookie_key';
        $cookieParam->param = 'param_name';
        $fakeGlobals = [
                '_COOKIE' => ['cookie_key' => '__COOKIE_VAL__']
            ];
        $assistedWebContextParam = new AssistedWebContextParam($cookieParam, new NoDefaultParam);
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose($fakeGlobals);
        $injector = (new \ReflectionClass(Injector::class))->newInstanceWithoutConstructor();
        /* @var Injector $injector */
        $param = $assistedWebContextParam->__invoke('a', [], $injector);
        $this->assertSame('__COOKIE_VAL__', $param);
    }
}

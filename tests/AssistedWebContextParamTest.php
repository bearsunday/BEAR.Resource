<?php

declare(strict_types=1);

namespace BEAR\Resource;

use LogicException;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use Ray\Di\InjectorInterface;
use Ray\WebContextParam\Annotation\CookieParam;
use ReflectionClass;

class AssistedWebContextParamTest extends TestCase
{
    public function testAssistedWebContextParam(): void
    {
        $cookieParam = new CookieParam('cookie_key', 'param_name');
        $fakeGlobals = [
            '_COOKIE' => ['cookie_key' => '__COOKIE_VAL__'],
        ];
        $assistedWebContextParam = new AssistedWebContextParam($cookieParam, new NoDefaultParam());
        AssistedWebContextParam::setSuperGlobalsOnlyForTestingPurpose($fakeGlobals);
        $injector = (new ReflectionClass(Injector::class))->newInstanceWithoutConstructor();
        if (! $injector instanceof InjectorInterface) {
            throw new LogicException();
        }

        $param = ($assistedWebContextParam)('a', [], $injector);
        $this->assertSame('__COOKIE_VAL__', $param);
    }
}

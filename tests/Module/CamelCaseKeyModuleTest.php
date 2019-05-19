<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\JsonSchema\FakeSnake;
use BEAR\Resource\JsonSchema\FakeUser;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class CamelCaseKeyModuleTest extends TestCase
{
    public function testCamelCaseKey()
    {
        $ro = $this->getRo(FakeSnake::class);
        $ro->onGet(20);
        $view = (string) $ro;
        $expectedCamelKeyView = '{"name":{"firstName":"mucha","lastName":"alfons"},"age":20}';
        $this->assertSame($expectedCamelKeyView, $view);
    }

    private function getRo(string $class)
    {
        $ro = (new Injector(new CamelCaseKeyModule, __DIR__ . '/tmp'))->getInstance($class);
        /* @var $ro FakeUser */
//        $ro->uri = new Uri('app://self/snake');

        return $ro;
    }
}

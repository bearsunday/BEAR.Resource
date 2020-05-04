<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\ResourceInterface;
use FakeVendor\Sandbox\Module\AppModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class HalModuleTest extends TestCase
{
    protected function setUp() : void
    {
        parent::setUp();
    }

    public function testConfigure() : void
    {
        $resource = (new Injector(new AppModule, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
        // request
        $news = $resource
            ->uri('app://self/news')
            ->withQuery(['date' => 'today'])
            ->request();
        $expect = '{
    "weather": {
        "today": "the weather of today is sunny"
    },
    "headline": "40th anniversary of Rubik\'s Cube invention.",
    "sports": "Pieter Weening wins Giro d\'Italia.",
    "user": {
        "id": 2,
        "name": "Aramis",
        "age": 16,
        "blog_id": 12
    }
}
';
        $this->assertSame($expect, (string) $news);
    }
}

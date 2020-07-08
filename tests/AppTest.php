<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class AppTest extends TestCase
{
    public function testGet(): void
    {
        $app = new AppAdapter(new Injector(), 'FakeVendor\Sandbox');
        $ro = $app->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $ro);
    }
}

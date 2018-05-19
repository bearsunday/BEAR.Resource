<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\Page\Index;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class AppTest extends TestCase
{
    public function testGet()
    {
        $app = new AppAdapter(new Injector, 'FakeVendor\Sandbox');
        $ro = $app->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $ro);
    }
}

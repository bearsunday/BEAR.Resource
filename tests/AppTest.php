<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\Page\Index;
use Ray\Di\Injector;

class AppTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $app = new AppAdapter(new Injector, 'FakeVendor\Sandbox');
        $resourceObject = $app->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $resourceObject);
    }
}

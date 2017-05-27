<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use FakeVendor\Sandbox\Module\AppModule;
use FakeVendor\Sandbox\Resource\Page\Index;
use Ray\Di\Injector;

class AppAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AppAdapter
     */
    private $appAdapter;

    protected function setUp()
    {
        $injector = new Injector(new AppModule, __DIR__ . '/tmp');
        $this->appAdapter = new AppAdapter($injector, 'FakeVendor\Sandbox');
    }

    public function testGet()
    {
        $index = $this->appAdapter->get(new Uri('page://self/index'));
        $this->assertInstanceOf(Index::class, $index);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\ResourceNotFoundException
     */
    public function testNotFound()
    {
        $index = $this->appAdapter->get(new Uri('page://self/__not_found__'));
    }
}

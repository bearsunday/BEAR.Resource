<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Module\ResourceModule;
use Ray\Di\Injector;

class ResourceParamHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $module = new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'));
        $this->resource = (new Injector($module, $_ENV['TMP_DIR']))->getInstance(ResourceInterface::class);
    }

    public function testResourceParam()
    {
        $instance = $this->resource->get->uri('app://self/rparam/greeting')->eager->request();
        $this->assertSame('LOGINID', $instance['name']);
    }

    public function testResourceParamInUriTemplate()
    {
        $instance = $this->resource->post->uri('app://self/rparam/greeting')->withQuery(['name' => 'BEAR'])->eager->request();
        $this->assertSame('login:BEAR', $instance['id']);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\ParameterException
     */
    public function testException()
    {
        $this->resource->put->uri('app://self/rparam/greeting')->eager->request();
    }

    public function testNullDefault()
    {
        $instance = $this->resource->get->uri('app://self/rparam/greeting')->withQuery(['name' => 'IGNORED'])->eager->request();
        $this->assertSame('LOGINID', $instance['name']);

    }
}

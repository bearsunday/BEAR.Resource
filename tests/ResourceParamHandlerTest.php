<?php

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
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

        $this->assertSame('sunday', $instance['name']);
    }

    public function testException()
    {
        $this->setExpectedException(ParameterException::class);
        $this->resource->put->uri('app://self/rparam/greeting')->eager->request();
    }
}

<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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

    public function testResourceParamInUriTemplate()
    {
        $instance = $this->resource->post->uri('app://self/rparam/greeting')->withQuery(['name' => 'BEAR'])->eager->request();

        $this->assertSame('login:BEAR', $instance['name']);
    }

    public function testException()
    {
        $this->setExpectedException(ParameterException::class, null, Code::BAD_REQUEST);
        $this->resource->put->uri('app://self/rparam/greeting')->eager->request();
    }
}

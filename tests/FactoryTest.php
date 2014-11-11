<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\Nop;
use Ray\Di\Injector;
use BEAR\Resource\Exception\Scheme;
use BEAR\Resource\Exception\ResourceNotFound;
use BEAR\Resource\Adapter\NopResource;
use FakeVendor\Sandbox\Resource\App\Factory\News;
use FakeVendor\Sandbox\Resource\App\User\Index;
use FakeVendor\Sandbox\Resource\Page\News as PageNews;
use BEAR\Resource\Exception\Uri as UriException;
use FakeVendor\Sandbox\Resource\Page\Index as IndexPage;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Factory
     */
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $injector = new Injector;
        $scheme = (new SchemeCollection)
            ->scheme('app')->host('self')->toAdapter(new Adapter\App($injector, 'FakeVendor\Sandbox', 'Resource\App'))
            ->scheme('page')->host('self')->toAdapter(new Adapter\App($injector, 'FakeVendor\Sandbox', 'Resource\Page'))
            ->scheme('prov')->host('self')->toAdapter(new Adapter\Prov)
            ->scheme('nop')->host('self')->toAdapter(new Nop);
        $this->factory = new Factory($scheme);
    }

    public function testNewInstanceNop()
    {
        $instance = $this->factory->newInstance('nop://self/path/to/dummy');
        $this->assertInstanceOf(NopResource::class, $instance);
    }

    public function testNewInstanceWithProvider()
    {
        $instance = $this->factory->newInstance('prov://self/path/to/dummy');
        $this->assertInstanceOf('\stdClass', $instance);
    }

    public function testNewInstanceScheme()
    {
        $this->setExpectedException(Scheme::class);
        $this->factory->newInstance('bad://self/news');
    }

    public function testNewInstanceSchemes()
    {
        $this->setExpectedException(Scheme::class);
        $this->factory->newInstance('app://invalid_host/news');
    }

    public function testNewInstanceApp()
    {
        $instance = $this->factory->newInstance('app://self/factory/news');
        $this->assertInstanceOf(News::class, $instance, get_class($instance));
    }

    public function testNewInstancePage()
    {
        $instance = $this->factory->newInstance('page://self/news');
        $this->assertInstanceOf(PageNews::class, $instance);
    }

    public function testInvalidUri()
    {
        $this->setExpectedException(UriException::class);
        $this->factory->newInstance('invalid_uri');
    }
    public function testInvalidObjectUri()
    {
        $this->setExpectedException(UriException::class);
        $this->factory->newInstance([]);
    }

    public function testResourceNotFound()
    {
        $this->setExpectedException(ResourceNotFound::class);
        $this->factory->newInstance('page://self/not_found_XXXX');
    }

    public function testIndexSuffix()
    {
        $instance = $this->factory->newInstance('page://self/');
        $this->assertInstanceOf(IndexPage::class, $instance);
    }
}

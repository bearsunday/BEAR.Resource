<?php

namespace BEAR\Resource;

use Ray\Di\Definition;
use Ray\Di\Injector;

/**
 * Test class for BEAR.Resource.
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $injector = Injector::create();
        $scheme = new SchemeCollection;
        $scheme->scheme('app')->host('self')->toAdapter(
            new Adapter\App($injector, 'Sandbox', 'Resource\App')
        );
        $scheme->scheme('page')->host('self')->toAdapter(
            new Adapter\App($injector, 'Sandbox', 'Resource\Page')
        );
        $scheme->scheme('nop')->host('self')->toAdapter(new Adapter\Nop);
        $scheme->scheme('prov')->host('self')->toAdapter(new Adapter\Prov);
        $this->factory = new Factory($scheme);
    }

    public function testNewFactory()
    {
        $this->assertInstanceOf('\BEAR\Resource\Factory', $this->factory);
    }

    public function testNewInstanceNop()
    {
        $instance = $this->factory->newInstance('nop://self/path/to/dummy');
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Nop', $instance);
    }

    public function testNewInstanceWithProvider()
    {
        $instance = $this->factory->newInstance('prov://self/path/to/dummy');
        $this->assertInstanceOf('\stdClass', $instance);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Scheme
     */
    public function testNewInstanceScheme()
    {
        $this->factory->newInstance('bad://self/news');
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Scheme
     */
    public function testNewInstanceSchemes()
    {
        $this->factory->newInstance('app://invalid_host/news');
    }


    public function testNewInstanceApp()
    {
        $instance = $this->factory->newInstance('app://self/news');
        $this->assertInstanceOf('Sandbox\Resource\App\News', $instance, get_class($instance));
    }

    public function testNewInstancePage()
    {
        $instance = $this->factory->newInstance('page://self/news');
        $this->assertInstanceOf('Sandbox\Resource\Page\News', $instance);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Uri
     */
    public function testInvalidUri()
    {
        $this->factory->newInstance('invalid_uri');
    }
}

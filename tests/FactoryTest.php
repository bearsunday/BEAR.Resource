<?php

namespace BEAR\Resource;

use Ray\Di\Annotation, Ray\Di\Config, Ray\Di\Forge, Ray\Di\Container, Ray\Di\Manager, Ray\Di\Injector, Ray\Di\EmptyModule, BEAR\Resource\factory;

/**
 * Test class for BEAR.Resource.
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    protected function setUp()
    {
        parent::setUp();
        $injector = new Injector(new Container(new Forge(new Config(new Annotation()))), new EmptyModule());
        $scheme = new SchemeCollection;
        $scheme->scheme('app')->host('self')->toAdapter(new \BEAR\Resource\Adapter\App($injector, 'testworld', 'ResourceObject'));
        $scheme->scheme('page')->host('self')->toAdapter(new \BEAR\Resource\Adapter\App($injector, 'testworld', 'Page'));
        $scheme->scheme('nop')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Nop);
        $scheme->scheme('prov')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Prov);
        $this->factory = new Factory($scheme);
    }

    public function test_Newfactory()
    {
        $this->assertInstanceOf('\BEAR\Resource\Factory', $this->factory);
    }

    public function test_newInstanceNop()
    {
        $instance = $this->factory->newInstance('nop://self/path/to/dummy');
        $this->assertInstanceOf('\BEAR\Resource\Adapter\Nop', $instance);
    }

    public function test_newInstanceWithProvider()
    {
        $instance = $this->factory->newInstance('prov://self/path/to/dummy');
        $this->assertInstanceOf('\stdClass', $instance);
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidScheme
     */
    public function test_newInstanceInvalidScheme()
    {
        $instance = $this->factory->newInstance('bad://self/news');
    }

    /**
     * @expectedException BEAR\Resource\Exception\InvalidScheme
     */
    public function test_newInstanceInvalidSchemes()
    {
        $instance = $this->factory->newInstance('app://invalid_host/news');
    }

    /**
     * @expectedException BEAR\Resource\Exception\Factory
     */
    public function test_Exception()
    {
        throw new Exception\Factory();
    }

    public function test_newInstanceApp()
    {
        $instance = $this->factory->newInstance('app://self/news');
        $this->assertInstanceOf('testworld\ResourceObject\news', $instance);
    }

    public function test_newInstancePage()
    {
        $instance = $this->factory->newInstance('page://self/news');
        $this->assertInstanceOf('testworld\Page\news', $instance);
    }

}
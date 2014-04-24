<?php

namespace BEAR\Resource\Adapter\Iterator;

class AppIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AppIterator
     */
    private $appIterator;

    protected function setUp()
    {
        $resourceDir = $_ENV['TEST_DIR'] . '/MyVendor';
        $this->appIterator = new AppIterator($resourceDir);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Resource\Adapter\Iterator\AppIterator', $this->appIterator);
    }

    public function testForEach()
    {
        $this->appIterator = new AppIterator($_ENV['TEST_DIR'] . '/MyVendor');
        foreach ($this->appIterator as $key => $meta) {
            $uri = filter_var($meta->uri, FILTER_VALIDATE_URL);
            $this->assertTrue((bool)$uri); // valid uri
            $this->assertInstanceOf('BEAR\Resource\Meta', $meta);
        }
    }

    /**
     * @expectedException \BEAR\Resource\Exception\ResourceDir
     */
    public function testException()
    {
        new AppIterator('/invalid');
    }
}

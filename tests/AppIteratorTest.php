<?php

namespace BEAR\Resource;

use  BEAR\Resource\Exception\ResourceDir;

class AppIteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AppIterator
     */
    private $appIterator;

    protected function setUp()
    {
        $resourceDir = $_ENV['TEST_DIR'] . '/Fake/MyVendor';
        $this->appIterator = new AppIterator($resourceDir);
    }

    public function testForEach()
    {
        foreach ($this->appIterator as $key => $meta) {
            $isValidUri = filter_var($meta->uri, FILTER_VALIDATE_URL);
            $this->assertTrue((bool) $isValidUri);
            $this->assertInstanceOf(Meta::class, $meta);
        }
    }

    public function testResourceDirException()
    {
        $this->setExpectedException(ResourceDir::class);
        new AppIterator('/invalid');
    }
}

<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\App;
use BEAR\Resource\Adapter\Nop;
use BEAR\Resource\Module\SchemeCollectionProvider;
use Ray\Di\Injector;

class SchemeCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SchemeCollection
     */
    private $scheme;

    protected function setUp()
    {
        parent::setUp();
        $this->scheme = new SchemeCollection;
    }

    public function testScheme()
    {
        $this->scheme->scheme('app')->host('self')->toAdapter(new Nop);
        $adapter = $this->scheme['app']['self'];
        $expected = 'BEAR\Resource\Adapter\Nop';
        $this->assertInstanceOf($expected, $adapter);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\AppName
     */
    public function testSchemeCollectionProvider()
    {
        $provider = new SchemeCollectionProvider;
        $provider->setAppName(null, '');
    }

    public function testIterator()
    {
        $injector = Injector::create();
        $resourceDir = $_ENV['TEST_DIR'] . '/MyVendor';
        $app = new App($injector, 'MyVendor\Sandbox', '', $resourceDir);

        $this->scheme->scheme('foo')->host('self')->toAdapter($app);
        $this->scheme->scheme('bar')->host('self')->toAdapter($app);
        $schemes = [];
        foreach ($this->scheme as $scheme) {
            $schemes[] = $scheme;
        }
        $this->assertSame(4, count($schemes));
    }
}

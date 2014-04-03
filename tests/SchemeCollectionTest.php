<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\Nop;
use BEAR\Resource\Module\SchemeCollectionProvider;

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
        $provider->setAppName(null);
    }
}

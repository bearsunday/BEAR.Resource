<?php

namespace BEAR\Resource;

use BEAR\Resource\Request\Method;
use BEAR\Resource\Adapter\Nop;
use Ray\Di\Config;

/**
 * Test class for BEAR.Resource.
 */
class SchemeCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->scheme = new SchemeCollection;
    }

    public function testScheme()
    {
        $this->scheme->scheme('app')->host('self')->toAdapter(new \BEAR\Resource\Adapter\Nop);
        $adapter = $this->scheme['app']['self'];
        $expected = 'BEAR\Resource\Adapter\Nop';
        $this->assertInstanceOf($expected, $adapter);
    }
}

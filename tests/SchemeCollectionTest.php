<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\Nop;
use BEAR\Resource\Exception\Scheme;

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
        $adapter = $this->scheme->getAdapter(new Uri('app://self/'));
        $this->assertInstanceOf(Nop::class, $adapter);
    }

    public function testInvalidScheme()
    {
        $this->setExpectedException(Scheme::class);
         $this->scheme->getAdapter(new Uri('app://self/'));
    }
}

<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\SchemeException;
use PHPUnit\Framework\TestCase;

class SchemeCollectionTest extends TestCase
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
        $this->scheme->scheme('app')->host('self')->toAdapter(new FakeNop);
        $adapter = $this->scheme->getAdapter(new Uri('app://self/'));
        $this->assertInstanceOf(FakeNop::class, $adapter);
    }

    public function testInvalidScheme()
    {
        $this->setExpectedException(SchemeException::class);
        $this->scheme->getAdapter(new Uri('app://self/'));
    }
}

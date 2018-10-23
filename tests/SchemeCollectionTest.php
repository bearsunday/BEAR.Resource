<?php

declare(strict_types=1);

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
        $this->expectException(SchemeException::class);
        $this->scheme->getAdapter(new Uri('app://self/'));
    }
}

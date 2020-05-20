<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Renderer\FakeTestRenderer;
use PHPUnit\Framework\TestCase;

class ObjectTest extends TestCase
{
    /**
     * @var ResourceObject
     */
    protected $ro;

    protected function setUp() : void
    {
        parent::setUp();
        $this->ro = new Mock\Entry;
        $this->ro[0] = 'entry1';
        $this->ro[1] = 'entry2';
        $this->ro[2] = 'entry3';
    }

    public function testOffsetGet() : void
    {
        $actual = $this->ro[0];
        $this->assertSame('entry1', $actual);
    }

    public function testOffsetExists() : void
    {
        $this->assertTrue(isset($this->ro[0]));
    }

    public function testOffsetUnset() : void
    {
        unset($this->ro[0]);
        $this->assertFalse(isset($this->ro[0]));
    }

    public function testOffsetExistsFalse() : void
    {
        $this->assertFalse(isset($this->ro[10]));
    }

    public function testCount() : void
    {
        $this->assertCount(3, $this->ro);
    }

    public function testKsort() : void
    {
        $this->ro = new Mock\Entry;
        $this->ro['d'] = 'lemon';
        $this->ro['a'] = 'orange';
        $this->ro['b'] = 'banana';
        $this->ro->ksort();
        $expected = ['a' => 'orange', 'b' => 'banana', 'd' => 'lemon'];
        $this->assertSame($expected, (array) $this->ro->body);
    }

    public function testAsort() : void
    {
        $this->ro = new Mock\Entry;
        $this->ro['d'] = 'lemon';
        $this->ro['a'] = 'orange';
        $this->ro['b'] = 'banana';
        $this->ro->asort();
        $expected = ['b' => 'banana', 'd' => 'lemon', 'a' => 'orange'];
        $this->assertSame($expected, (array) $this->ro->body);
    }

    public function testAsortDisable() : void
    {
        $resource = new Mock\Entry;
        $resource->body = 1;
        $resource->asort();
        $this->assertSame(1, $resource->body);
    }

    public function testKsortDisable() : void
    {
        $resource = new Mock\Entry;
        $resource->body = 1;
        $resource->ksort();
        $this->assertSame(1, $resource->body);
    }

    public function testAppend() : void
    {
        $this->ro[] = 'entry_append';
        $this->assertCount(4, $this->ro->body);
    }

    public function testGetIterator() : void
    {
        $iterator = $this->ro->getIterator();
        $actual = '';
        while ($iterator->valid()) {
            $actual .= $iterator->key() . '=>' . $iterator->current() . ',';
            $iterator->next();
        }
        $expected = '0=>entry1,1=>entry2,2=>entry3,';
        $this->assertSame($expected, $actual);
    }

    public function testGetEmptyIterator() : void
    {
        $this->ro->body = 'string';
        $iterator = $this->ro->getIterator();
        $actual = '';
        while ($iterator->valid()) {
            $actual .= $iterator->key() . '=>' . $iterator->current() . ',';
            $iterator->next();
        }
        $expected = '';
        $this->assertSame($expected, $actual);
    }

    public function testCode() : void
    {
        $this->assertSame(Code::OK, 200);
        $this->assertSame(Code::BAD_REQUEST, 400);
        $this->assertSame(Code::ERROR, 500);
    }

    public function testToString() : void
    {
        $this->ro->headers['X-TEST'] = __FUNCTION__;
        $str = (string) $this->ro;
        $this->assertIsString($str);
    }

    public function testToStringScalarBody() : void
    {
        $this->ro->headers['X-TEST'] = __FUNCTION__;
        $this->ro->body = 'OK';
        $str = (string) $this->ro;
        $this->assertIsString($str);
    }

    public function testToStringWithRenderer() : void
    {
        $renderer = new FakeTestRenderer;
        $this->ro->setRenderer($renderer);
        $result = (string) ($this->ro);
        $this->assertSame('["entry1","entry2","entry3"]', $result);
    }

    public function testSetRendererWithoutRenderer() : void
    {
        $result = (string) ($this->ro);
        $this->assertSame('["entry1","entry2","entry3"]', $result);
    }

    public function estResourceHasView() : void
    {
        $this->ro->view = 'view-is-override';
        $result = (string) ($this->ro);
        $this->assertSame('view-is-override', $result);
    }
}

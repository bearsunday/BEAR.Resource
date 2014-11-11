<?php

namespace BEAR\Resource;

use BEAR\Resource\Renderer\FakeTestRenderer;

/**
 * Test class for BEAR.Resource.
 */
class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceObject
     */
    protected $resource;

    protected function setUp()
    {
        parent::setUp();
        $this->resource = new Mock\Entry;
        $this->resource[0] = 'entry1';
        $this->resource[1] = 'entry2';
        $this->resource[2] = 'entry3';
    }

    public function testOffsetGet()
    {
        $actual = $this->resource[0];
        $this->assertSame('entry1', $actual);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->resource[0]));
    }

    public function testOffsetUnset()
    {
        unset($this->resource[0]);
        $this->assertFalse(isset($this->resource[0]));
    }

    public function testOffsetExistsFalse()
    {
        $this->assertFalse(isset($this->resource[10]));
    }

    public function testCount()
    {
        $this->assertSame(3, count($this->resource));
    }

    public function testKsort()
    {
        $this->resource = new Mock\Entry;
        $this->resource['d'] = 'lemon';
        $this->resource['a'] = 'orange';
        $this->resource['b'] = 'banana';
        $this->resource->ksort();
        $expected = array('a' => 'orange', 'b' => 'banana', 'd' => 'lemon');
        $this->assertSame($expected, (array) $this->resource->body);
    }

    public function testAsort()
    {
        $this->resource = new Mock\Entry;
        $this->resource['d'] = 'lemon';
        $this->resource['a'] = 'orange';
        $this->resource['b'] = 'banana';
        $this->resource->asort();
        $expected = array('b' => 'banana', 'd' => 'lemon', 'a' => 'orange');
        $this->assertSame($expected, (array) $this->resource->body);
    }

    public function testAsortDisable()
    {
        $resource = new Mock\Entry;
        $resource->body = 1;
        $resource->asort();
        $this->assertSame(1, $resource->body);
    }

    public function testKsortDisable()
    {
        $resource = new Mock\Entry;
        $resource->body = 1;
        $resource->ksort();
        $this->assertSame(1, $resource->body);
    }


    public function testAppend()
    {
        $this->resource[] = 'entry_append';
        $this->assertSame(4, count($this->resource->body));
    }

    public function testGetIterator()
    {
        $iterator = $this->resource->getIterator();
        $actual = '';
        while ($iterator->valid()) {
            $actual .= $iterator->key() . '=>' . $iterator->current() . ",";
            $iterator->next();
        }
        $expected = '0=>entry1,1=>entry2,2=>entry3,';
        $this->assertSame($expected, $actual);
    }

    public function testGetEmptyIterator()
    {
        $this->resource->body = 'string';
        $iterator = $this->resource->getIterator();
        $actual = '';
        while ($iterator->valid()) {
            $actual .= $iterator->key() . '=>' . $iterator->current() . ",";
            $iterator->next();
        }
        $expected = '';
        $this->assertSame($expected, $actual);
    }

    public function testCode()
    {
        $this->assertSame(Code::OK, 200);
        $this->assertSame(Code::BAD_REQUEST, 400);
        $this->assertSame(Code::ERROR, 500);
    }

    public function testToString()
    {
        $this->resource->headers['X-TEST'] = __FUNCTION__;
        $str = (string) $this->resource;
        $this->assertTrue(is_string($str));
    }

    public function testToStringScalarBody()
    {
        $this->resource->headers['X-TEST'] = __FUNCTION__;
        $this->resource->body = 'OK';
        $str = (string) $this->resource;
        $this->assertTrue(is_string($str));
    }

    public function testToStringWithRenderer()
    {
        $renderer = new FakeTestRenderer;
        $this->resource->setRenderer($renderer);
        $result = (string) ($this->resource);
        $this->assertSame('["entry1","entry2","entry3"]', $result);
    }

    public function testSetRendererWithoutRenderer()
    {
        $result = (string) ($this->resource);
        $this->assertSame('', $result);
    }

    public function testResourceHasView()
    {
        $view = 'i have view';
        $this->resource->view = $view;
        $result = (string) ($this->resource);
        $this->assertSame($view, $result);
    }
}

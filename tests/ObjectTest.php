<?php

namespace BEAR\Resource;

use BEAR\Resource\Renderer\FakeTestRenderer;

class ObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceObject
     */
    protected $resourceObject;

    protected function setUp()
    {
        parent::setUp();
        $this->resourceObject = new Mock\Entry;
        $this->resourceObject[0] = 'entry1';
        $this->resourceObject[1] = 'entry2';
        $this->resourceObject[2] = 'entry3';
    }

    public function testOffsetGet()
    {
        $actual = $this->resourceObject[0];
        $this->assertSame('entry1', $actual);
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->resourceObject[0]));
    }

    public function testOffsetUnset()
    {
        unset($this->resourceObject[0]);
        $this->assertFalse(isset($this->resourceObject[0]));
    }

    public function testOffsetExistsFalse()
    {
        $this->assertFalse(isset($this->resourceObject[10]));
    }

    public function testCount()
    {
        $this->assertSame(3, count($this->resourceObject));
    }

    public function testKsort()
    {
        $this->resourceObject = new Mock\Entry;
        $this->resourceObject['d'] = 'lemon';
        $this->resourceObject['a'] = 'orange';
        $this->resourceObject['b'] = 'banana';
        $this->resourceObject->ksort();
        $expected = array('a' => 'orange', 'b' => 'banana', 'd' => 'lemon');
        $this->assertSame($expected, (array) $this->resourceObject->body);
    }

    public function testAsort()
    {
        $this->resourceObject = new Mock\Entry;
        $this->resourceObject['d'] = 'lemon';
        $this->resourceObject['a'] = 'orange';
        $this->resourceObject['b'] = 'banana';
        $this->resourceObject->asort();
        $expected = array('b' => 'banana', 'd' => 'lemon', 'a' => 'orange');
        $this->assertSame($expected, (array) $this->resourceObject->body);
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
        $this->resourceObject[] = 'entry_append';
        $this->assertSame(4, count($this->resourceObject->body));
    }

    public function testGetIterator()
    {
        $iterator = $this->resourceObject->getIterator();
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
        $this->resourceObject->body = 'string';
        $iterator = $this->resourceObject->getIterator();
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
        $this->resourceObject->headers['X-TEST'] = __FUNCTION__;
        $str = (string) $this->resourceObject;
        $this->assertTrue(is_string($str));
    }

    public function testToStringScalarBody()
    {
        $this->resourceObject->headers['X-TEST'] = __FUNCTION__;
        $this->resourceObject->body = 'OK';
        $str = (string) $this->resourceObject;
        $this->assertTrue(is_string($str));
    }

    public function testToStringWithRenderer()
    {
        $renderer = new FakeTestRenderer;
        $this->resourceObject->setRenderer($renderer);
        $result = (string) ($this->resourceObject);
        $this->assertSame('["entry1","entry2","entry3"]', $result);
    }

    public function testSetRendererWithoutRenderer()
    {
        $result = (string) ($this->resourceObject);
        $this->assertSame('', $result);
    }

    public function testResourceHasView()
    {
        $view = 'i have view';
        $this->resourceObject->view = $view;
        $result = (string) ($this->resourceObject);
        $this->assertSame($view, $result);
    }
}

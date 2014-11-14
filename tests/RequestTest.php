<?php

namespace BEAR\Resource;

use BEAR\Resource\Adapter\FakeNop;
use BEAR\Resource\Adapter\FakeNopResource;
use BEAR\Resource\Exception\LogicException;
use BEAR\Resource\Exception\OutOfBounds;
use BEAR\Resource\Exception\Method;
use BEAR\Resource\Renderer\FakeErrorRenderer;
use BEAR\Resource\Renderer\FakeTestRenderer;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\Sandbox\Resource\App\User\Entry;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        parent::setUp();
        $this->invoker = new Invoker(new NamedParameter);
        $this->request = new Request($this->invoker, new Entry);
    }

    public function testToUriWithMethod()
    {
        $request = new Request(
            $this->invoker,
            new FakeResource,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $actual = $request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=koriym&b=25', $actual);
    }

    public function testToUri()
    {
        $request = new Request(
            $this->invoker,
            new FakeResource,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $actual = $request->toUri();
        $this->assertSame('test://self/path/to/resource?a=koriym&b=25', $actual);
    }

    public function testInvoke()
    {
        $request = new Request(
            $this->invoker,
            new FakeNopResource,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $this->assertSame(['koriym', 25], $request()->body);
    }

    public function testOffsetSet()
    {
        $this->setExpectedException(OutOfBounds::class);
        $request = new Request(
            $this->invoker,
            new FakeNopResource,
            Request::GET,
            ['key' => 'animal', 'value' => 'kuma']
        );
        $request['animal'] = 'cause_exception';
    }

    public function testOffsetUnset()
    {
        $this->setExpectedException(OutOfBounds::class);
        $request = new Request(
            $this->invoker,
            new FakeNopResource,
            Request::PUT,
            ['key' => 'animal', 'value' => 'kuma']
        );
        unset($request['animal']);
    }

    public function testInvokeWithQuery()
    {
        $request = new Request(
            $this->invoker,
            new FakeNopResource,
            'get', ['a' => 'koriym', 'b' => 25]
        );
        $this->assertSame(['koriym', 30], $request(['b' => 30])->body);
    }

    public function testToStringWithRenderableResourceObject()
    {
        $ro = (new FakeResource)->setRenderer(new FakeTestRenderer);
        $request = new Request(
            $this->invoker,
            $ro,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $this->assertSame(['koriym', 30], $request(['b' => 30])->body['posts']);
        $this->assertSame('{"posts":["koriym",30]}', (string) $request);
    }

    public function testToStringWithErrorRenderer()
    {
        $ro = (new FakeResource)->setRenderer(new FakeErrorRenderer);
        $request = new Request(
            $this->invoker,
            $ro,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $this->assertSame('', (string) $request);
    }

    public function testToStringWithoutRender()
    {
        $request = new Request(
            $this->invoker,
            new FakeResource,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $result = (string) $request;
        $this->assertSame('', $result);
    }

    public function testIterator()
    {
        $request = new Request($this->invoker, new Entry);
        foreach ($request as $row) {
            $result[] = $row;
        }
        $expected = [
            0 => ['id' => 100, 'title' => 'Entry1'],
            1 => ['id' => 101, 'title' => 'Entry2'],
            2 => ['id' => 102, 'title' => 'Entry3']
        ];
        $this->assertSame($expected, $result);
    }

    public function testArrayAccess()
    {
        $request = new Request($this->invoker, new Entry);
        $result = $request[100];
        $expected = [
            'id' => 100,
            'title' => 'Entry1'
        ];
        $this->assertSame($expected, $result);
    }

    public function testArrayAccessNotExists()
    {
        $this->setExpectedException(OutOfBounds::class);
        $request = new Request(
            $this->invoker,
            new Entry
        );
        $request[0];
    }

    public function testIsSet()
    {
        $request = new Request($this->invoker, new Entry);
        $result = isset($request[100]);
        $this->assertTrue($result);
    }

    public function testIsSetNot()
    {
        $request = new Request(
            $this->invoker,
            new Entry
        );
        $result = isset($request[0]);
        $this->assertFalse($result);
    }

    public function testWithQuery()
    {
        $this->request->withQuery(['a' => 'bear']);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=bear', $actual);
    }

    public function testAddQuery()
    {
        $this->request->withQuery(['a' => 'original', 'b' => 25]);
        $this->request->addQuery(['a' => 'bear', 'c' => 'kuma']);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=bear&b=25&c=kuma', $actual);
    }

    public function testInvalidMethod()
    {
        $this->setExpectedException(Method::class);
        new Request($this->invoker, new Entry, 'invalid-method');
    }

    public function testHash()
    {
        $this->assertInternalType('string', $this->request->hash());
    }
}

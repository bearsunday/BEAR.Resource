<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodException;
use BEAR\Resource\Exception\OutOfBoundsException;
use BEAR\Resource\Renderer\FakeErrorRenderer;
use BEAR\Resource\Renderer\FakeTestRenderer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use FakeVendor\Sandbox\Resource\App\User\Entry;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class RequestTest extends TestCase
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var FakeResource
     */
    private $fake;

    protected function setUp()
    {
        parent::setUp();
        $this->invoker = new Invoker(new NamedParameter(new ArrayCache, new AnnotationReader, new Injector), new OptionsRenderer(new OptionsMethods(new AnnotationReader)));
        $entry = new Entry;
        $entry->uri = new Uri('test://self/path/to/resource');
        $this->request = new Request($this->invoker, $entry);
        $this->fake = new FakeResource;
        $this->fake->uri = new Uri('test://self/path/to/resource');
    }

    public function testToUriWithMethod()
    {
        $request = new Request(
            $this->invoker,
            $this->fake,
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
            $this->fake,
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
        $this->expectException(OutOfBoundsException::class);
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
        $this->expectException(OutOfBoundsException::class);
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
            'get',
            ['a' => 'koriym', 'b' => 25]
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
            $this->fake,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $result = (string) $request;
        $this->assertSame('{"posts":["koriym",25]}', $result);
    }

    public function testIterator()
    {
        $request = new Request($this->invoker, new Entry);
        $result = [];
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
        $this->expectException(OutOfBoundsException::class);
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
        $this->expectException(MethodException::class);
        new Request($this->invoker, new Entry, 'invalid-method');
    }

    public function testHash()
    {
        $this->assertInternalType('string', $this->request->hash());
    }

    public function testRequestExceptionString()
    {
        $request = new Request(
            $this->invoker,
            new FakeNopResource,
            Request::PUT,
            ['key' => 'animal', 'value' => 'kuma']
        );
        $this->assertSame('', (string) $request);
    }

    public function testSerialize()
    {
        $ro = unserialize(serialize($this->request));
        $this->assertInstanceOf(AbstractRequest::class, $ro);
    }

    public function testCode()
    {
        $request = new Request(
            $this->invoker,
            $this->fake,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $newRequest = clone $request;
        $code = $newRequest->code;
        $this->assertSame(200, $code);

        return $request;
    }

    /**
     * @depends testCode
     */
    public function testHeaders(Request $request)
    {
        $headers = $request->headers;
        $this->assertSame([], $headers);
    }

    /**
     * @depends testCode
     */
    public function testBody(Request $request)
    {
        $body = $request->body;
        $expected = ['posts' => ['koriym', 25]];
        $this->assertSame($expected, $body);
    }

    /**
     * @depends testCode
     */
    public function testInvalidProp(Request $request)
    {
        $this->expectException(\OutOfRangeException::class);
        $request->__invalid__;
    }
}

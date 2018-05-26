<?php

declare(strict_types=1);
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

    /**
     * @var Entry
     */
    private $entry;

    /**
     * @var FakeNopResource
     */
    private $nop;

    protected function setUp()
    {
        parent::setUp();
        $this->invoker = new Invoker(new NamedParameter(new NamedParamMetas(new ArrayCache, new AnnotationReader), new Injector), new OptionsRenderer(new OptionsMethods(new AnnotationReader)));
        $entry = new Entry;
        $entry->uri = new Uri('test://self/path/to/resource');
        $this->request = new Request($this->invoker, $entry);
        $this->fake = new FakeResource;
        $this->fake->uri = new Uri('test://self/path/to/resource');
        $entry = new Entry;
        $entry->uri = new Uri('app://self/dummy');
        $this->entry = $entry;
        $nop = new FakeNopResource;
        $nop->uri = new Uri('app://self/dummy');
        $this->nop = $nop;
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
            $this->nop,
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
            $this->nop,
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
            $this->nop,
            Request::PUT,
            ['key' => 'animal', 'value' => 'kuma']
        );
        unset($request['animal']);
    }

    public function testInvokeWithQuery()
    {
        $request = new Request(
            $this->invoker,
            $this->nop,
            'get',
            ['a' => 'koriym', 'b' => 25]
        );
        $this->assertSame(['koriym', 30], $request(['b' => 30])->body);
    }

    public function testToStringWithRenderableResourceObject()
    {
        $ro = (new FakeResource)->setRenderer(new FakeTestRenderer);
        $ro->uri = new Uri('app://self/dummy');
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
        $ro->uri = new Uri('app://self/dummy');
        $request = new Request(
            $this->invoker,
            $ro,
            Request::GET,
            ['a' => 'koriym', 'b' => 25]
        );
        $no = $str = '';
        set_error_handler(function (int $errno, string $errstr) use (&$no, &$str) {
            $no = $errno;
            $str = $errstr;
        });
        (string) $request;
        $this->assertSame(256, $no);
        $this->assertContains('FakeErrorRenderer->render', $str);
        $this->assertSame('', (string) $request);
        restore_error_handler();
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
        $request = new Request($this->invoker, $this->entry);
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
        $request = new Request($this->invoker, $this->entry);
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
            $this->entry
        );
        $request[0];
    }

    public function testIsSet()
    {
        $request = new Request($this->invoker, $this->entry);
        $result = isset($request[100]);
        $this->assertTrue($result);
    }

    public function testIsSetNot()
    {
        $request = new Request(
            $this->invoker,
            $this->entry
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
        new Request($this->invoker, $this->entry, 'invalid-method');
    }

    public function testHash()
    {
        $this->assertInternalType('string', $this->request->hash());
    }

    public function testRequestExceptionString()
    {
        $request = new Request(
            $this->invoker,
            $this->nop,
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

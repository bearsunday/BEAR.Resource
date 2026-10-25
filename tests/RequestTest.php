<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\OutOfBoundsException;
use BEAR\Resource\Renderer\FakeErrorRenderer;
use BEAR\Resource\Renderer\FakeTestRenderer;
use FakeVendor\Sandbox\Resource\App\User\Entry;
use LogicException;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use stdClass;
use ValueError;

use function iterator_to_array;
use function restore_error_handler;
use function serialize;
use function set_error_handler;

class RequestTest extends TestCase
{
    protected Request $request;
    private Invoker $invoker;
    private FakeResource $fake;
    private Entry $entry;
    private FakeNopResource $nop;

    protected function setUp(): void
    {
        $this->invoker = (new InvokerFactory())();
        $entry = new Entry();
        $entry->uri = new Uri('test://self/path/to/resource');
        $this->request = new Request($this->invoker, $entry);
        $this->fake = new FakeResource();
        $this->fake->uri = new Uri('test://self/path/to/resource');
        $entry = new Entry();
        $entry->uri = new Uri('app://self/dummy');
        $this->entry = $entry;
        $nop = new FakeNopResource();
        $nop->uri = new Uri('app://self/dummy');
        $this->nop = $nop;
    }

    public function testToUriWithMethod(): void
    {
        $request = new Request(
            $this->invoker,
            $this->fake,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        $actual = $request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=koriym&b=25', $actual);
    }

    public function testToUri(): void
    {
        $request = new Request(
            $this->invoker,
            $this->fake,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        $actual = $request->toUri();
        $this->assertSame('test://self/path/to/resource?a=koriym&b=25', $actual);
    }

    public function testInvoke(): void
    {
        $request = new Request(
            $this->invoker,
            $this->nop,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        $this->assertSame(['koriym', 25], $request()->body);
    }

    public function testOffsetSet(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $request = new Request(
            $this->invoker,
            $this->nop,
            Method::GET,
            ['key' => 'animal', 'value' => 'kuma'],
        );
        $request['animal'] = 'cause_exception';
    }

    public function testOffsetUnset(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $request = new Request(
            $this->invoker,
            $this->nop,
            Method::PUT,
            ['key' => 'animal', 'value' => 'kuma'],
        );
        unset($request['animal']);
    }

    public function testInvokeWithQuery(): void
    {
        $request = new Request(
            $this->invoker,
            $this->nop,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        $this->assertSame(['koriym', 30], $request(['b' => 30])->body);
    }

    public function testOffsetGetNullValue(): void
    {
        $request = new Request(
            $this->invoker,
            $this->fake,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );

        $this->assertNull($request['nullValue']);
    }

    public function testToStringWithRenderableResourceObject(): void
    {
        $ro = new FakeResource();
        $ro->setRenderer(new FakeTestRenderer());
        $ro->uri = new Uri('app://self/dummy');
        $request = new Request(
            $this->invoker,
            $ro,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        $this->assertSame(['koriym', 30], $request(['b' => 30])->body['posts']);  // @phpstan-ignore-line
        $this->assertSame('{"posts":["koriym",30],"nullValue":null}', (string) $request);
    }

    public function testToStringWithErrorRenderer(): void
    {
        $ro = new FakeResource();
        $ro->setRenderer(new FakeErrorRenderer());
        $ro->uri = new Uri('app://self/dummy');
        $request = new Request(
            $this->invoker,
            $ro,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        set_error_handler(static function (int $errno, string $errstr) use (&$no, &$str): bool {
            $no = $errno;
            $str = $errstr;

            return true;
        });
        (string) $request;
        $this->assertSame('', (string) $request);
        restore_error_handler();
    }

    public function testToStringWithoutRender(): void
    {
        $request = new Request(
            $this->invoker,
            $this->fake,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        $result = (string) $request;
        $this->assertSame('{"posts":["koriym",25],"nullValue":null}', $result);
    }

    public function testIterator(): void
    {
        $request = new Request($this->invoker, $this->entry);
        $result = [];
        foreach ($request as $row) {
            $result[] = $row;
        }

        $expected = [
            0 => ['id' => 100, 'title' => 'Entry1'],
            1 => ['id' => 101, 'title' => 'Entry2'],
            2 => ['id' => 102, 'title' => 'Entry3'],
        ];
        $this->assertSame($expected, $result);
    }

    public function testIteratorWithNonArrayBody(): void
    {
        $ro = new class extends ResourceObject {
            public function onGet(): static
            {
                $this->body = new stdClass();

                return $this;
            }
        };
        $ro->uri = new Uri('app://self/object-body');
        $request = new Request($this->invoker, $ro);

        $this->assertSame([], iterator_to_array($request));
    }

    public function testArrayAccess(): void
    {
        $request = new Request($this->invoker, $this->entry);
        $result = $request[100]; // @phpstan-ignore-line
        $expected = [
            'id' => 100,
            'title' => 'Entry1',
        ];
        $this->assertSame($expected, $result);
    }

    public function testArrayAccessNotExists(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $request = new Request(
            $this->invoker,
            $this->entry,
        );
        $request[0]; // @phpstan-ignore-line
    }

    public function testIsSet(): void
    {
        $request = new Request($this->invoker, $this->entry);
        $result = isset($request[100]); // @phpstan-ignore-line
        $this->assertTrue($result);
    }

    public function testIsSetNot(): void
    {
        $request = new Request(
            $this->invoker,
            $this->entry,
        );
        $result = isset($request[0]); // @phpstan-ignore-line
        $this->assertFalse($result);
    }

    public function testWithQuery(): void
    {
        $this->request->withQuery(['a' => 'bear']);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=bear', $actual);
    }

    public function testAddQuery(): void
    {
        $this->request->withQuery(['a' => 'original', 'b' => 25]);
        $this->request->addQuery(['a' => 'bear', 'c' => 'kuma']);
        $actual = $this->request->toUriWithMethod();
        $this->assertSame('get test://self/path/to/resource?a=bear&b=25&c=kuma', $actual);
    }

    public function testWithQueryAfterAReadReExecutesWithTheNewQuery(): void
    {
        $request = new Request($this->invoker, $this->nop, Method::GET, ['a' => 'koriym', 'b' => 25]);
        $this->assertSame(['koriym', 25], $request->body);

        $request->withQuery(['a' => 'bear', 'b' => 1]);

        $this->assertSame(['bear', 1], $request->body);
    }

    public function testAddQueryAfterAReadReExecutesWithTheMergedQuery(): void
    {
        $request = new Request($this->invoker, $this->nop, Method::GET, ['a' => 'koriym', 'b' => 25]);
        $this->assertSame(['koriym', 25], $request->body);

        $request->addQuery(['b' => 30]);

        $this->assertSame(['koriym', 30], $request->body);
    }

    public function testInvokeWithQueryAfterAReadIsSeenByTheNextRead(): void
    {
        $request = new Request($this->invoker, $this->nop, Method::GET, ['a' => 'koriym', 'b' => 25]);
        $this->assertSame(['koriym', 25], $request->body);

        $request(['b' => 30]);

        $this->assertSame(['koriym', 30], $request->body);
    }

    public function testInvalidMethod(): void
    {
        $this->expectException(ValueError::class);
        Method::from('invalid-method');
    }

    public function testHash(): void
    {
        $this->assertIsString($this->request->hash());
    }

    public function testRequestExceptionString(): void
    {
        $request = new Request(
            $this->invoker,
            $this->nop,
            Method::PUT,
            ['key' => 'animal', 'value' => 'kuma'],
        );
        $no = $str = '';
        set_error_handler(static function (int $errno, string $errstr) use (&$no, &$str): bool {
            $no = $errno;
            $str = $errstr;

            return true;
        });
        (string) $request;
        $this->assertSame(256, $no);
        $this->assertStringContainsString(FakeNopResource::class, $str);
        restore_error_handler();
    }

    public function testSerialize(): void
    {
        $this->expectException(LogicException::class);
        serialize($this->request);
    }

    public function testCode(): Request
    {
        $request = new Request(
            $this->invoker,
            $this->fake,
            Method::GET,
            ['a' => 'koriym', 'b' => 25],
        );
        $newRequest = clone $request;
        $code = $newRequest->code;
        $this->assertSame(200, $code);

        return $request;
    }

    #[Depends('testCode')]
    public function testHeaders(Request $request): void
    {
        $headers = $request->headers;
        $this->assertSame([], $headers);
    }

    #[Depends('testCode')]
    public function testBody(Request $request): void
    {
        $body = $request->body;
        $expected = ['posts' => ['koriym', 25], 'nullValue' => null];
        $this->assertSame($expected, $body);
    }

    #[Depends('testCode')]
    public function testInvalidProp(Request $request): void
    {
        $this->expectException(OutOfRangeException::class);
        $request->invalid; // @phpstan-ignore-line
    }
}

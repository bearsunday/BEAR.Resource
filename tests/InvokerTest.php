<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Interceptor\FakeLogInterceptor;
use BEAR\Resource\Mock\Blog;
use BEAR\Resource\Mock\Comment;
use BEAR\Resource\Mock\Json;
use FakeVendor\Sandbox\Resource\App\Doc;
use FakeVendor\Sandbox\Resource\App\Restbucks\Order;
use FakeVendor\Sandbox\Resource\App\User;
use FakeVendor\Sandbox\Resource\App\Weave\Book;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;

class InvokerTest extends TestCase
{
    /**
     * @var Invoker
     */
    protected $invoker;

    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var Request
     */
    protected $request;

    protected function setUp() : void
    {
        $this->invoker = (new InvokerFactory)();
    }

    public function testInvoke() : void
    {
        $request = new Request($this->invoker, (new FakeRo)(new User), Request::GET, ['id' => 1]);
        $actual = $this->invoker->invoke($request)->body;
        $expected = ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12];
        $this->assertSame($actual, $expected);
    }

    public function testInvokerInterfaceDefaultParam() : void
    {
        $request = new Request($this->invoker, (new FakeRo)(new User), Request::POST, ['id' => 1]);
        $actual = $this->invoker->invoke($request)->body;
        $expected = 'post user[1 default_name 99]';
        $this->assertSame($actual, $expected);
    }

    public function testInvokerInterfaceDefaultParamWithNoProvider() : void
    {
        $this->expectException(ParameterException::class);
        $request = new Request($this->invoker, (new FakeRo)(new User), Request::PUT);
        $this->invoker->invoke($request);
    }

    public function testInvokerInterfaceWithNoProvider() : void
    {
        $this->expectException(ParameterException::class);
        $request = new Request($this->invoker, (new FakeRo)(new Blog), Request::GET, []);
        $this->invoker->invoke($request);
    }

    public function testInvokerInterfaceWithUnspecificProviderButNoResult() : void
    {
        $this->expectException(ParameterException::class);
        $request = new Request($this->invoker, (new FakeRo)(new Comment));
        $actual = $this->invoker->invoke($request);
        $this->assertSame('entry1', $actual);
    }

    public function testInvokeWeave() : void
    {
        $compiler = new Compiler(__DIR__ . '/tmp');
        $book = $compiler->newInstance(Book::class, [], (new Bind)->bindInterceptors('onGet', [new FakeLogInterceptor]));
        if (! $book instanceof Book) {
            throw new \LogicException;
        }

        $request = new Request($this->invoker, (new FakeRo)($book), Request::GET, ['id' => 1]);
        $actual = $this->invoker->invoke($request)->body;
        $expected = "book id[1][Log] target = FakeVendor\\Sandbox\\Resource\\App\\Weave\\Book, input = Array\n(\n    [0] => 1\n)\n, result = book id[1]";
        $this->assertSame($expected, $actual);
    }

    public function testOptionsMethod() : string
    {
        $ro = new Doc;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $response = $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST, DELETE';
        $this->assertSame($actual, $expected);

        return (string) $response->view;
    }

    /**
     * @depends testOptionsMethod
     */
    public function testOptionsMethodBody(string $view) : void
    {
        $expected = '{
    "GET": {
        "summary": "User",
        "description": "Returns a variety of information about the user specified by the required $id parameter",
        "request": {
            "parameters": {
                "id": {
                    "type": "string",
                    "description": "User ID"
                }
            },
            "required": [
                "id"
            ]
        },
        "links": [
            {
                "rel": "friend",
                "href": "/fiend{?id}",
                "method": "get",
                "title": "Friend profile"
            },
            {
                "rel": "task",
                "href": "/task{?id}",
                "method": "get"
            }
        ],
        "embed": [
            {
                "rel": "profile",
                "src": "/profile{?id}"
            }
        ]
    },
    "POST": {
        "request": {
            "parameters": {
                "id": {
                    "type": "integer",
                    "description": "ID"
                },
                "name": {
                    "type": "string",
                    "description": "Name",
                    "default": "default_name"
                },
                "age": {
                    "type": "integer",
                    "description": "Age",
                    "default": "99"
                }
            },
            "required": [
                "id"
            ]
        }
    },
    "DELETE": []
}
';
        $this->assertSame($expected, $view);
    }

    public function testOptionsMethod2() : void
    {
        $ro = new Order;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST';
        $this->assertSame($actual, $expected);
    }

    public function testOptionsWeaver() : void
    {
        $ro = (new Compiler(__DIR__ . '/tmp'))->newInstance(Order::class, [], new Bind);
        if (! $ro instanceof Order) {
            throw new \LogicException;
        }
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST';
        $this->assertSame($actual, $expected);
    }

    public function testInvokeExceptionHandle() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $outOfRangeId = 4;
        $request = new Request($this->invoker, new User, Request::GET, ['id' => $outOfRangeId]);
        $this->invoker->invoke($request);
    }

    public function testInvalidMethod() : void
    {
        $this->expectException(MethodNotAllowedException::class);
        $request = new Request($this->invoker, new Order, Request::DELETE);
        $this->invoker->invoke($request);
    }

    public function testOptionsNotAllowed() : void
    {
        $this->expectException(MethodNotAllowedException::class);
        $request = new Request($this->invoker, new Order, Request::DELETE);
        $this->invoker->invoke($request);
    }

    public function testInvokeClassTyped() : void
    {
        $person = ['age' => 28, 'name' => 'monsley'];
        $request = new Request($this->invoker, (new FakeRo)(new Json), Request::GET, ['specialPerson' => $person]);
        $actual = $this->invoker->invoke($request)->body;
        $this->assertSame($actual->name, 'monsley');
        $this->assertSame($actual->age, 28);
    }

    public function testInvokeClassTypedSnakeCase() : void
    {
        $person = ['age' => 28, 'name' => 'monsley'];
        $request = new Request($this->invoker, (new FakeRo)(new Json), Request::GET, ['special_person' => $person]);
        $actual = $this->invoker->invoke($request)->body;
        $this->assertSame($actual->name, 'monsley');
        $this->assertSame($actual->age, 28);
    }

    public function testInvokeClassTypedSnakeParamException() : void
    {
        $this->expectException(ParameterException::class);
        $request = new Request($this->invoker, new Json, Request::GET, []);
        $this->invoker->invoke($request);
    }
}

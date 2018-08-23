<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Interceptor\FakeLogInterceptor;
use BEAR\Resource\Mock\Comment;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use FakeVendor\Sandbox\Resource\App\Doc;
use FakeVendor\Sandbox\Resource\App\Restbucks\Order;
use FakeVendor\Sandbox\Resource\App\User;
use FakeVendor\Sandbox\Resource\App\Weave\Book;
use PHPUnit\Framework\TestCase;
use Ray\Aop\Bind;
use Ray\Aop\Compiler;
use Ray\Di\Injector;

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

    protected function setUp()
    {
        $this->invoker = new Invoker(new NamedParameter(new NamedParamMetas(new ArrayCache, new AnnotationReader), new Injector), new OptionsRenderer(new OptionsMethods(new AnnotationReader)));
    }

    public function testInvoke()
    {
        $request = new Request($this->invoker, new User, Request::GET, ['id' => 1]);
        $actual = $this->invoker->invoke($request)->body;
        $expected = ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12];
        $this->assertSame($actual, $expected);
    }

    public function testInvokerInterfaceDefaultParam()
    {
        $request = new Request($this->invoker, new User, Request::POST, ['id' => 1]);
        $actual = $this->invoker->invoke($request)->body;
        $expected = 'post user[1 default_name 99]';
        $this->assertSame($actual, $expected);
    }

    public function testInvokerInterfaceDefaultParamWithNoProvider()
    {
        $this->expectException(ParameterException::class);
        $request = new Request($this->invoker, new User, Request::PUT);
        $this->invoker->invoke($request);
    }

    public function testInvokerInterfaceWithNoProvider()
    {
        $this->expectException(ParameterException::class);
        $request = new Request($this->invoker, new Mock\Blog, Request::GET, []);
        $this->invoker->invoke($request);
    }

    public function testInvokerInterfaceWithUnspecificProviderButNoResult()
    {
        $this->expectException(ParameterException::class);
        $request = new Request($this->invoker, new Comment);
        $actual = $this->invoker->invoke($request);
        $this->assertSame('entry1', $actual);
    }

    public function testInvokeWeave()
    {
        $compiler = new Compiler($_ENV['TMP_DIR']);
        $book = $compiler->newInstance(Book::class, [], (new Bind)->bindInterceptors('onGet', [new FakeLogInterceptor]));
        if (! $book instanceof Book) {
            throw new \LogicException;
        }
        $request = new Request($this->invoker, $book, Request::GET, ['id' => 1]);
        $actual = $this->invoker->invoke($request)->body;
        $expected = "book id[1][Log] target = FakeVendor\\Sandbox\\Resource\\App\\Weave\\Book, input = Array\n(\n    [0] => 1\n)\n, result = book id[1]";
        $this->assertSame($expected, $actual);
    }

    public function testOptionsMethod()
    {
        $ro = new Doc;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $response = $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST, DELETE';
        $this->assertSame($actual, $expected);

        return $response->view;
    }

    /**
     * @depends testOptionsMethod
     */
    public function testOptionsMethodBody(string $view)
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
        }
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

    public function testOptionsMethod2()
    {
        $ro = new Order;
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST';
        $this->assertSame($actual, $expected);
    }

    public function testOptionsWeaver()
    {
        $ro = (new Compiler($_ENV['TMP_DIR']))->newInstance(Order::class, [], new Bind);
        if (! $ro instanceof Order) {
            throw new \LogicException;
        }
        $request = new Request($this->invoker, $ro, Request::OPTIONS);
        $this->invoker->invoke($request);
        $actual = $ro->headers['Allow'];
        $expected = 'GET, POST';
        $this->assertSame($actual, $expected);
    }

    public function testInvokeExceptionHandle()
    {
        $this->expectException(\InvalidArgumentException::class);
        $outOfRangeId = 4;
        $request = new Request($this->invoker, new User, Request::GET, ['id' => $outOfRangeId]);
        $this->invoker->invoke($request);
    }

    public function testInvalidMethod()
    {
        $this->expectException(MethodNotAllowedException::class);
        $request = new Request($this->invoker, new Order, Request::DELETE);
        $this->invoker->invoke($request);
    }

    public function testOptionsNotAllowed()
    {
        $this->expectException(MethodNotAllowedException::class);
        $invoker = new Invoker(new NamedParameter(new NamedParamMetas(new ArrayCache, new AnnotationReader), new Injector), new VoidOptionsRenderer);
        $request = new Request($this->invoker, new Order, Request::DELETE);
        $invoker->invoke($request);
    }
}

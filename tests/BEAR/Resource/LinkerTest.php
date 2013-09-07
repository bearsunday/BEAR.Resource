<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Ray\Di\Definition;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Forge;
use Ray\Di\Container;
use Ray\Di\Injector;
use Ray\Di\EmptyModule;

use BEAR\Resource\Adapter\Nop;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

use Sandbox\Resource\App\Link\Author as LinkUser;
use Sandbox\Resource\App\Link\User;

/**
 * Test class for BEAR.Resource.
 */
class LinkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    protected function setUp()
    {
        parent::setUp();

        $this->linker = new Linker(new Reader);
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParams(new SignalParam($signal, new Param));
        $invoker = new Invoker($this->linker, $params);
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new Reader)))), new EmptyModule);

        $this->request = new Request($invoker);
        $scheme = (new SchemeCollection)
            ->scheme('app')
            ->host('self')
            ->toAdapter(new Adapter\App($injector, 'Sandbox', 'Resource\App')
        );
        $factory = new Factory($scheme);
        $this->resource = new Resource($factory, $invoker, new Request($invoker));
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Linker', $this->linker);
    }

    public function testLinkAnnotationSelf()
    {
        $this->request->links = [new LinkType('blog', LinkType::SELF_LINK)];
        $this->request->method = 'get';
        $ro = new User;
        $ro->body = $ro->onGet(1);
        $this->request->ro = $ro;

        $result = $this->linker->invoke($this->request);
        $expected = [
            'name' => 'Aramis blog'
        ];
        $this->assertSame($expected, $result->body);

        return $this->request;
    }

    /**
     * @param Request $request
     *
     *  testLinkAnnotationSelf
     */
    public function testAnnotationNew()
    {
        $this->request->links = [new LinkType('blog', LinkType::NEW_LINK)];
        $this->request->method = 'get';
        $ro = new User;
        $ro->body = $ro->onGet(1);
        $this->request->ro = $ro;

        $result = $this->linker->invoke($this->request);
        $expected = [
            [
                'name' => 'Aramis',
                'age' => 16,
                'blog_id' => 12,
            ],
            [
                'name' => 'Aramis blog',
            ],
        ];
        $this->assertSame($expected, $result->body);
    }
}

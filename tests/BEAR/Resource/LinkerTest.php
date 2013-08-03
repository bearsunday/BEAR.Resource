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
use testworld\ResourceObject\User;

/**
 * Test class for BEAR.Resource.
 */
class LinkerTest extends \PHPUnit_Framework_TestCase
{
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
        $scheme = new SchemeCollection;
        $scheme->scheme('app')->host('self')->toAdapter(
            new Adapter\App($injector, 'testworld', 'ResourceObject')
        );
        $factory = new Factory($scheme);
        $this->resource = new Resource($factory, $invoker, new Request($invoker));
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\Linker', $this->linker);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\BadLinkRequest
     */
    public function testLinkException()
    {
        $ro = new Mock\Link;
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'UNAVAILABLE';
        $links = [$link];
        $this->request->links = $links;
        $this->request->method = 'get';
        $this->linker->invoke($ro, $this->request, $ro->onGet(1));
    }

    public function testLinkSelf1()
    {
        $ro = new Mock\Link;
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'View';
        $links = [$link];
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
        $expected = '<html>bear1</html>';
        $this->assertSame($expected, $result);
    }

    public function testLinkSelf2()
    {
        $ro = new User;
        $ro->setResource($this->resource);
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'Blog';
        $links = [$link];
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
        //         $expected = '<html><html>bear1</html></html>';
        $expected = array(
            'id' => 12,
            'name' => 'Aramis blog',
            'inviter' => 2
        );
        $this->assertSame($expected, $result);
    }

    public function testLinkNew1()
    {
        $ro = new User;
        $ro->setResource($this->resource);
        $link = new LinkType;
        $link->type = LinkType::NEW_LINK;
        $link->key = 'Blog';
        $links = [$link];
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
        $expected = array(
            0 => array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12),
            1 => array('id' => 12, 'name' => 'Aramis blog', 'inviter' => 2)
        );
        $this->assertSame($expected, $result);
    }

    public function testLinkCrawl()
    {
        $ro = new User;
        $ro->setResource($this->resource);
        $link = new LinkType;
        $link->type = LinkType::CRAWL_LINK;
        $link->key = 'Blog';
        $links = [$link];
        $result = $ro->onGet(1);
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $result);
        $expected = array(
            'id' => 2,
            'name' => 'Aramis',
            'age' => 16,
            'blog_id' => 12,
            'Blog' => array(
                'id' => 12,
                'name' => 'Aramis blog',
                'inviter' => 2,
            )
        );
        $this->assertSame($expected, $result);
    }

    public function testSelfLinkSelf()
    {
        $ro = new User;
        $ro->setResource($this->resource);
        $links = [];
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'Blog';
        $links[] = $link;
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'Inviter';
        $links[] = $link;
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
        $expected = ['id' => 3, 'name' => 'Porthos', 'age' => 17, 'blog_id' => 0];
        $this->assertSame($expected, $result);
    }

    public function testSelfLinkTarget()
    {
        $ro = new User;
        $ro->setResource($this->resource);
        $links = [];
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'Blog';
        $links[] = $link;
        $link = new LinkType;
        $link->type = LinkType::NEW_LINK;
        $link->key = 'Inviter';
        $links[] = $link;
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
        $expected = [
            0 => ['id' => 12, 'name' => 'Aramis blog', 'inviter' => 2],
            1 => ['id' => 3, 'name' => 'Porthos', 'age' => 17, 'blog_id' => 0]
        ];
        $this->assertSame($expected, $result);
    }

    public function testTargetLinkTarget()
    {
        $ro = new User;
        $ro->setResource($this->resource);
        $links = [];
        $link = new LinkType;
        $link->type = LinkType::NEW_LINK;
        $link->key = 'Blog';
        $links[] = $link;
        $link = new LinkType;
        $link->type = LinkType::NEW_LINK;
        $link->key = 'Inviter';
        $links[] = $link;
        $result = $ro->onGet(1);
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $result);
        $expected = array(
            array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12),
            array('id' => 12, 'name' => 'Aramis blog', 'inviter' => 2),
            array('id' => 3, 'name' => 'Porthos', 'age' => 17, 'blog_id' => 0)
        );
        $this->assertSame($expected, $result);
    }

    public function testTargetLinkSelf()
    {
        $ro = new User;
        $ro->setResource($this->resource);
        $links = [];
        $link = new LinkType;
        $link->type = LinkType::NEW_LINK;
        $link->key = 'Blog';
        $links[] = $link;
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'Inviter';
        $links[] = $link;
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
        $expected = array(
            0 => array('id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12),
            1 => array('id' => 12, 'name' => 'Aramis blog', 'inviter' => 2)
        );
        $this->assertSame($expected, $result);
    }

    public function testListCrawlBasic()
    {
        $ro = new User\Entry;
        $link = new LinkType;
        $link->type = LinkType::CRAWL_LINK;
        $link->key = 'Comment';
        $links = [$link];
        $result = $ro->onGet(1);
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $result);
        $expected = array(
            100 => array(
                'id' => 100,
                'title' => 'Entry1',
                'Comment' => array(
                    'comment_id' => 200,
                    'body' => 'entry 100 comment',
                ),
            ),
            101 => array(
                'id' => 101,
                'title' => 'Entry2',
                'Comment' => array(
                    'comment_id' => 201,
                    'body' => 'entry 101 comment',
                ),
            ),
            102 => array(
                'id' => 102,
                'title' => 'Entry3',
                'Comment' => array(
                    'comment_id' => 202,
                    'body' => 'entry 102 comment',
                ),
            )
        );
        $this->assertSame($expected, $result);
    }

    public function testListCrawlThenCrawl()
    {
        $ro = new User\Entry;
        $links = [];
        $link = new LinkType;
        $link->type = LinkType::CRAWL_LINK;
        $link->key = 'Comment';
        $links[] = $link;
        $link = new LinkType;
        $link->type = LinkType::CRAWL_LINK;
        $link->key = 'ThumbsUp';
        $links[] = $link;
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
        $expected = array(
            100 => array(
                'id' => 100,
                'title' => 'Entry1',
                'Comment' => array(
                    'comment_id' => 200,
                    'body' => 'entry 100 comment',
                    'ThumbsUp' => array(
                        'up' => 30,
                        'down' => 10,
                        'body' => 'like for 200 comment',
                    ),
                ),
            ),
            101 => array(
                'id' => 101,
                'title' => 'Entry2',
                'Comment' => array(
                    'comment_id' => 201,
                    'body' => 'entry 101 comment',
                    'ThumbsUp' => array(
                        'up' => 30,
                        'down' => 10,
                        'body' => 'like for 201 comment',
                    ),
                ),
            ),
            102 => array(
                'comment_id' => 202,
                'body' => 'entry 102 comment',
                'ThumbsUp' => array(
                    'up' => 30,
                    'down' => 10,
                    'body' => 'like for 202 comment',
                ),
            ),
        );
        $this->assertSame($expected, $result);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Link
     */
    public function testReturnInsideInstanceAfterListLink()
    {
        $ro = new User\Entry;
        $links = [];
        $link = new LinkType;
        $link->type = LinkType::CRAWL_LINK;
        $link->key = 'Comment';
        $links[] = $link;
        $link = new LinkType;
        $link->type = LinkType::CRAWL_LINK;
        $link->key = 'Point';
        $links[] = $link;
        $this->request->links = $links;
        $this->request->method = 'get';
        $this->linker->invoke($ro, $this->request, $ro->onGet(1));
    }
}

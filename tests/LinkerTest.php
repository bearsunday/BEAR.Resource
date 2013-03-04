<?php

namespace BEAR\Resource;

use Ray\Di\Definition;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Forge;
use Ray\Di\Container;
use Ray\Di\Manager;
use Ray\Di\Injector;
use Ray\Di\EmptyModule;

use BEAR\Resource\Request\Method;
use BEAR\Resource\Adapter\Nop;
use Doctrine\Common\Annotations\AnnotationReader as Reader;

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
        $injector = new Injector(new Container(new Forge(new Config(new Annotation(new Definition, new Reader)))), new EmptyModule);
        $signal = require dirname(__DIR__) . '/vendor/aura/signal/scripts/instance.php';
        $this->request = new Request(new Invoker(new Config(new Annotation(new Definition, new Reader)), new Linker(new Reader), $signal));
        $invoker = new Invoker(new Config(new Annotation(new Definition, new Reader)), new Linker(new Reader), $signal);
        $scheme = new SchemeCollection;
        $scheme->scheme('app')->host('self')->toAdapter(
            new \BEAR\Resource\Adapter\App($injector, 'testworld', 'ResourceObject')
        );
        $factory = new Factory($scheme);
        $this->resource = new Resource($factory, $invoker, new Request($invoker));
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Linker', $this->linker);
    }

    /**
     * @expectedException BEAR\Resource\Exception\BadLinkRequest
     */
    public function test_linkException()
    {
        $ro = new Mock\Link;
        $link = new LinkType;
        $link->type = LinkType::SELF_LINK;
        $link->key = 'UNAVAILABLE';
        $links = [$link];
        $this->request->links = $links;
        $this->request->method = 'get';
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
    }

    public function test_linkSelf1()
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

    public function test_linkSelf2()
    {
        $ro = new \testworld\ResourceObject\User;
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

    public function test_linkNew1()
    {
        $ro = new \testworld\ResourceObject\User;
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

    public function test_linkCrawl()
    {
        $ro = new \testworld\ResourceObject\User;
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

    public function test_SelfLinkSelf()
    {
        $ro = new \testworld\ResourceObject\User;
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

    public function test_SelfLinkTarget()
    {
        $ro = new \testworld\ResourceObject\User;
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

    public function test_TargeLinktTarget()
    {
        $ro = new \testworld\ResourceObject\User;
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

    public function test_TargeLinktSelf()
    {
        $ro = new \testworld\ResourceObject\User;
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

    public function test_ListCrawlBasic()
    {
        $ro = new \testworld\ResourceObject\User\Entry;
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

    public function test_ListCrawlThenCrawl()
    {
        $ro = new \testworld\ResourceObject\User\Entry;
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
                        'body' => 'thumbsup for 200 comment',
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
                        'body' => 'thumbsup for 201 comment',
                    ),
                ),
            ),
            102 => array(
                'comment_id' => 202,
                'body' => 'entry 102 comment',
                'ThumbsUp' => array(
                    'up' => 30,
                    'down' => 10,
                    'body' => 'thumbsup for 202 comment',
                ),
            ),
        );
        $this->assertSame($expected, $result);
    }

    /**
     * @expectedException BEAR\Resource\Exception\Link
     */
    public function test_ReturnInsideInstanceAfterListLink()
    {
        $ro = new \testworld\ResourceObject\User\Entry;
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
        $result = $this->linker->invoke($ro, $this->request, $ro->onGet(1));
    }
}

<?php

namespace BEAR\Resource;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;
use Guzzle\Parser\UriTemplate\UriTemplate;
use Ray\Di\Definition;
use Ray\Di\Injector;

use BEAR\Resource\Adapter\Nop;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use Sandbox\Resource\App\Link\Scalar\Name;
use Sandbox\Resource\App\Link\User;
use Sandbox\Resource\App\Marshal\Author;

/**
 * Test class for BEAR.Resource.
 */
class LinkerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Linker
     */
    private $linker;

    /**
     * @var Resource
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();

        $this->linker = new Linker(new Reader);
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $params = new NamedParams(new SignalParam($signal, new Param));
        $invoker = new Invoker($this->linker, $params);
        $injector = $GLOBALS['INJECTOR'];

        $this->request = new Request($invoker);
        $scheme = (new SchemeCollection)
            ->scheme('app')
            ->host('self')
            ->toAdapter(new Adapter\App($injector, 'Sandbox', 'Resource\App')
            );
        $factory = new Factory($scheme);
        $this->resource = new Resource($factory, $invoker, new Request($invoker), new Anchor(new UriTemplate, new Reader, $this->request));
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

    public function testAnnotationCrawl()
    {
        $this->request->links = [new LinkType('tree', LinkType::CRAWL_LINK)];
        $this->request->method = 'get';
        $ro = new Author;
        $ro->body = $ro->onGet(1);
        $this->request->ro = $ro;

        $result = $this->linker->invoke($this->request);
        $expected = array (
            'id' => 1,
            'name' => 'Aramis',
            'post' =>
            array (
                0 =>
                array (
                    'id' => '1',
                    'author_id' => '1',
                    'body' => 'Anna post #1',
                    'meta' =>
                    array (
                        0 =>
                        array (
                            'id' => '1',
                            'post_id' => '1',
                            'data' => 'meta 1',
                        ),
                    ),
                    'tag' =>
                    array (
                        0 =>
                        array (
                            'id' => '1',
                            'post_id' => '1',
                            'tag_id' => '1',
                            'tag_name' =>
                            array (
                                0 =>
                                array (
                                    'id' => '1',
                                    'name' => 'zim',
                                ),
                            ),
                        ),
                        1 =>
                        array (
                            'id' => '2',
                            'post_id' => '1',
                            'tag_id' => '2',
                            'tag_name' =>
                            array (
                                0 =>
                                array (
                                    'id' => '2',
                                    'name' => 'dib',
                                ),
                            ),
                        ),
                    ),
                ),
                1 =>
                array (
                    'id' => '2',
                    'author_id' => '1',
                    'body' => 'Anna post #2',
                    'meta' =>
                    array (
                        0 =>
                        array (
                            'id' => '2',
                            'post_id' => '2',
                            'data' => 'meta 2',
                        ),
                    ),
                    'tag' =>
                    array (
                        0 =>
                        array (
                            'id' => '3',
                            'post_id' => '2',
                            'tag_id' => '2',
                            'tag_name' =>
                            array (
                                0 =>
                                array (
                                    'id' => '2',
                                    'name' => 'dib',
                                ),
                            ),
                        ),
                        1 =>
                        array (
                            'id' => '4',
                            'post_id' => '2',
                            'tag_id' => '3',
                            'tag_name' =>
                            array (
                                0 =>
                                array (
                                    'id' => '3',
                                    'name' => 'gir',
                                ),
                            ),
                        ),
                    ),
                ),
                2 =>
                array (
                    'id' => '3',
                    'author_id' => '1',
                    'body' => 'Anna post #3',
                    'meta' =>
                    array (
                        0 =>
                        array (
                            'id' => '3',
                            'post_id' => '3',
                            'data' => 'meta 3',
                        ),
                    ),
                    'tag' =>
                    array (
                        0 =>
                        array (
                            'id' => '5',
                            'post_id' => '3',
                            'tag_id' => '3',
                            'tag_name' =>
                            array (
                                0 =>
                                array (
                                    'id' => '3',
                                    'name' => 'gir',
                                ),
                            ),
                        ),
                        1 =>
                        array (
                            'id' => '6',
                            'post_id' => '3',
                            'tag_id' => '1',
                            'tag_name' =>
                            array (
                                0 =>
                                array (
                                    'id' => '1',
                                    'name' => 'zim',
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        $this->assertSame($expected, $result->body);
    }


    /**
     * @expectedException \BEAR\Resource\Exception\LinkQuery
     */
    public function testScalarValueLinkThrowException()
    {
        $this->request->links = [new LinkType('greeting', LinkType::NEW_LINK)];
        $this->request->method = 'get';
        $ro = new Name;
        $ro->body = $ro->onGet('koriym');
        $this->request->ro = $ro;
        $this->linker->invoke($this->request);
    }
    /**
     * @expectedException \BEAR\Resource\Exception\LinkRel
     */
    public function testInvalidRel()
    {
        $this->request->links = [new LinkType('xxx', LinkType::NEW_LINK)];
        $this->request->method = 'get';
        $ro = new User;
        $ro->body = $ro->onGet(1);
        $this->request->ro = $ro;
        $this->linker->invoke($this->request);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\LinkQuery
     */
    public function testInvalidLinkQuery()
    {
        $this->request->links = [new LinkType('no_query', LinkType::NEW_LINK)];
        $this->request->method = 'get';
        $ro = new Name;
        $ro->body = [];
        $this->request->ro = $ro;
        $this->linker->invoke($this->request);
    }
}

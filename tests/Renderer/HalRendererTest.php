<?php

namespace BEAR\Resource;


use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Injector;

class HalRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @var HalRenderer
     */
    private $hal;

    public function setUp()
    {
        $this->hal = new HalRenderer(new AnnotationReader());
    }

    public function testRender()
    {
        $ro = new FakeAuthor();
        $ro->uri = new Uri('app://self/author?id=1');
        $ro->uri->method = 'get';
        $ro->setRenderer($this->hal);
        $ro->onGet(1);
       $result = (string) $ro;
        $expect = '{
    "id": 1,
    "friend_id": "f1",
    "org_id": "o1",
    "_links": {
        "self": {
            "href": "/author?id=1"
        },
        "friend": {
            "href": "/friend?id=f1"
        },
        "org": {
            "href": "/org?id=o1"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }

    public function testRenderPost()
    {
        $ro = new FakeAuthor();
        $ro->uri = new Uri('app://self/author');
        $ro->uri->method = 'post';
        $ro->setRenderer($this->hal);
        $ro->onPost(1);
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '{
    "id": 1,
    "friend_id": "f1",
    "_links": {
        "self": {
            "href": "/author"
        },
        "friend": {
            "href": "/friend?id=f1"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }

    public function testRenderScalar()
    {
        $ro = new FakeScalar;
        $ro->uri = new Uri('app://self/scalar');
        $ro->uri->method = 'get';
        $ro->setRenderer($this->hal);
        $ro->onGet();
        $result = (string) $ro;
        $expect = '{
    "value": "abc",
    "_links": {
        "self": {
            "href": "/scalar"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }
}

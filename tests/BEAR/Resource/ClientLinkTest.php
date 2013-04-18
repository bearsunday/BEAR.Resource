<?php

namespace BEAR\Resource;

use Ray\Di\Injector;

class ClientLinkTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->resource = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
        $scheme = new SchemeCollection;
        $injector = Injector::create();
        $scheme->scheme('app')->host('self')->toAdapter(new Adapter\App($injector, 'sandbox', 'App'));
        $this->resource->setSchemeCollection($scheme);
        $this->user = $this->resource->newInstance('app://self/Link/User');
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\ObjectInterface', $this->user);
    }

    public function test_LinkSelf()
    {
        $blog = $this
            ->resource
            ->get
            ->uri('app://self/Link/User')
            ->withQuery(['id' => 1])
            ->linkSelf("blog")
            ->eager
            ->request()
            ->body;
        $expected = array(
            'id' => 12,
            'name' => 'Aramis blog',
            'inviter' => 2,
        );
        $this->assertSame($expected, $blog);
    }
}

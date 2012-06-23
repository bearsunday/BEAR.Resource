<?php

namespace BEAR\Resource;

use BEAR\Resource\Mock\MockModule;

class ClientLinkTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->resource = require dirname(__DIR__) . '/scripts/instance.php';
        $this->user = $this->resource->newInstance('app://self/Link/User');
    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Object', $this->user);
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
        ->request()->body;
        $expected = array (
                        'id' => 12,
                        'name' => 'Aramis blog',
                        'inviter' => 2,
        );
        $this->assertSame($expected, $blog);
    }
}

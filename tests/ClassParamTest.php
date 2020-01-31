<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Module\AppModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ClassParamTest extends TestCase
{
    /** @var ResourceInterface */
    private $resource;

    protected function setUp()
    {
        parent::setUp();

        $this->resource = (new Injector(new AppModule, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
    }

    public function testClassParam()
    {
        // request
        $user = $this->resource->get('app://self/user/search', ['country' => 'Japan', 'post_count' => '10']);
        $this->assertSame(Code::OK, $user->code);
        $this->assertSame('Japan', $user['country']);
        $this->assertGreaterThan(10, $user['post_count']);
    }

    public function testClassParamOverrideDefaultValue()
    {
        $user = $this->resource->get('app://self/user/search', ['activated' => '0']);
        $this->assertSame(Code::OK, $user->code);
        $this->assertSame(0, $user['activated']);
    }
}

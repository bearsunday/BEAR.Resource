<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Exception\UriException;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ResourceParamHandlerTest extends TestCase
{
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $module = new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'));
        $this->resource = (new Injector($module, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
    }

    public function testResourceParam(): void
    {
        $instance = $this->resource->get('app://self/rparam/greeting');
        $this->assertSame('LOGINID', $instance['name']);
    }

    public function testResourceParamInUriTemplate(): void
    {
        $instance = $this->resource->post('app://self/rparam/greeting', ['name' => 'BEAR']);
        $this->assertSame('login:BEAR', $instance['id']);
    }

    public function testException(): void
    {
        $this->expectException(ParameterException::class);
        $this->resource->put('app://self/rparam/greeting');
    }

    public function testNullDefault(): void
    {
        $instance = $this->resource->get('app://self/rparam/greeting', ['name' => 'IGNORED']);
        $this->assertSame('LOGINID', $instance['name']);
    }

    public function testALinkInTheFragmentIsNotSupported(): void
    {
        $this->expectException(UriException::class);
        $this->resource->get('app://self/rparam/linked');
    }
}

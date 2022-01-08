<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ResourceParamHandlerTest extends TestCase
{
    private \BEAR\Resource\ResourceInterface $resource;

    protected function setUp(): void
    {
        parent::setUp();
        $module = new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'));
        $this->resource = (new Injector($module, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class); // @phpstan-ignore-line
    }

    public function testResourceParam(): void
    {
        $instance = $this->resource->get->uri('app://self/rparam/greeting')->eager->request();
        $this->assertSame('LOGINID', $instance['name']);
    }

    public function testResourceParamInUriTemplate(): void
    {
        $instance = $this->resource->post->uri('app://self/rparam/greeting')->withQuery(['name' => 'BEAR'])->eager->request();
        $this->assertSame('login:BEAR', $instance['id']);
    }

    public function testException(): void
    {
        $this->expectException(ParameterException::class);
        $this->resource->put->uri('app://self/rparam/greeting')->eager->request();
    }

    public function testNullDefault(): void
    {
        $instance = $this->resource->get->uri('app://self/rparam/greeting')->withQuery(['name' => 'IGNORED'])->eager->request();
        $this->assertSame('LOGINID', $instance['name']);
    }
}

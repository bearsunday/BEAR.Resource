<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class ResourceParamHandlerTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        $module = new FakeSchemeModule(new ResourceModule('FakeVendor\Sandbox'));
        $this->resource = (new Injector($module, $_ENV['TMP_DIR']))->getInstance(ResourceInterface::class);
    }

    public function testResourceParam()
    {
        $instance = $this->resource->get->uri('app://self/rparam/greeting')->eager->request();
        $this->assertSame('LOGINID', $instance['name']);
    }

    public function testResourceParamInUriTemplate()
    {
        $instance = $this->resource->post->uri('app://self/rparam/greeting')->withQuery(['name' => 'BEAR'])->eager->request();
        $this->assertSame('login:BEAR', $instance['id']);
    }

    public function testException()
    {
        $this->expectException(ParameterException::class);
        $this->resource->put->uri('app://self/rparam/greeting')->eager->request();
    }

    public function testNullDefault()
    {
        $instance = $this->resource->get->uri('app://self/rparam/greeting')->withQuery(['name' => 'IGNORED'])->eager->request();
        $this->assertSame('LOGINID', $instance['name']);
    }
}

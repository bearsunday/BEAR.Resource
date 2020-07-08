<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Doc;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{
    /** @var Meta */
    private $meta;

    protected function setUp(): void
    {
        parent::setUp();
        $this->meta = new Meta(Doc::class);
    }

    public function testUri(): void
    {
        $this->assertSame('app://self/doc', $this->meta->uri);
    }

    public function testAllow(): void
    {
        $this->assertSame(['get', 'post', 'delete'], $this->meta->options->allow);
    }

    public function testParams(): void
    {
        /** @var Params $params */
        $params = $this->meta->options->params[1];
        $this->assertSame('post', $params->method);
        $this->assertSame(['id'], $params->required);
        $this->assertSame(['name', 'age'], $params->optional);
    }
}

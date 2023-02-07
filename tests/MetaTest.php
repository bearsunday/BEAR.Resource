<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Doc;
use PHPUnit\Framework\TestCase;

use function assert;

class MetaTest extends TestCase
{
    private Meta $meta;

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
        $params = $this->meta->options->params[1];
        assert($params instanceof Params);
        $this->assertSame('post', $params->method);
        $this->assertSame(['id'], $params->required);
        $this->assertSame(['name', 'age'], $params->optional);
    }
}

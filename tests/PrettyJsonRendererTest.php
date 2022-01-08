<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Module\EmbedResourceModule;
use BEAR\Resource\Module\ResourceModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;

class PrettyJsonRendererTest extends TestCase
{
    private \BEAR\Resource\PrettyJsonRenderer $renderer;

    public function setUp(): void
    {
        $this->renderer = new PrettyJsonRenderer();
    }

    public function testRender(): void
    {
        $ro = new NullResourceObject();
        $ro->body = ['a' => ['b' => 'c']];
        $this->assertSame('{
    "a": {
        "b": "c"
    }
}
', $this->renderer->render($ro));
    }

    public function testRenderWithEmbeded(): void
    {
        $resource = (new Injector(new EmbedResourceModule(new ResourceModule('FakeVendor\Sandbox')), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $ro = $resource->get('app://self/bird/embed-birds', ['id' => '1']);
        $this->assertSame('{
    "birds": {
        "bird1": {
            "name": "chill kun"
        },
        "bird2": {
            "sparrow_id": "1"
        }
    }
}
', $this->renderer->render($ro));
    }
}

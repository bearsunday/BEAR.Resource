<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullRendererTest extends TestCase
{
    public function testToString(): void
    {
        $this->assertSame('', (new NullRenderer())->render(new NullResourceObject()));
    }
}

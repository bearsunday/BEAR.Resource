<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullRendererTest extends TestCase
{
    public function test__toString() : void
    {
        $this->assertSame('', (new NullRenderer)->render(new NullResourceObject));
    }
}

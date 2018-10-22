<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullRendererTest extends TestCase
{
    public function test__toString()
    {
        $this->assertInstanceOf(NullResourceObject::class, (new NullRenderer)->render(new NullResourceObject));
    }
}

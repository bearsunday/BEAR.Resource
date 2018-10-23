<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullUriTest extends TestCase
{
    public function test__toString()
    {
        $this->assertSame('app://self/index', (string) new NullUri);
    }
}

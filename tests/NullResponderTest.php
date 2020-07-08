<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullResponderTest extends TestCase
{
    public function testToString(): void
    {
        $this->assertNull((new NullResponder())(new NullResourceObject(), [])); // @phpstan-ignore-line
    }
}

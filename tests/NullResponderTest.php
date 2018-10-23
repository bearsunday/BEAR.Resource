<?php

declare(strict_types=1);

namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullResponderTest extends TestCase
{
    public function test__toString()
    {
        $this->assertNull((new NullResponder)(new NullResourceObject, []));
    }
}

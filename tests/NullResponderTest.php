<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use PHPUnit\Framework\TestCase;

class NullResponderTest extends TestCase
{
    public function test__toString()
    {
        $this->assertNull((new NullResponder)(new NullResourceObject, []));
    }
}

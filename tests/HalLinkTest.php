<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;

class HalLinkTest extends TestCase
{
    /**
     * @covers \BEAR\Resource\HalLink::linkAnnotation()
     */
    public function testBodyLinkInvalidLink(): void
    {
        $halLink = new HalLink(new NullReverseLink());
        $body = [
            '_links' => [
                ['rel' => 'not-href'],
            ],
        ];
        $hal = $halLink->addHalLink($body, [], new Hal());
        $this->assertInstanceOf(Hal::class, $hal);
    }
}

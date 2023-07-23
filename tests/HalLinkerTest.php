<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Doctrine\Common\Annotations\AnnotationReader;
use Nocarrier\Hal;
use PHPUnit\Framework\TestCase;

/** @deprecated */
class HalLinkerTest extends TestCase
{
    /** @covers \BEAR\Resource\HalLink::bodyLink() */
    public function testBodyLinkInvalidLink(): void
    {
        $halLink = new HalLinker(new NullReverseLinker());
        $body = [
            '_links' => ['rel1' => 'not-href'],
        ];
        $hal = $halLink->addHalLink($body, [], new Hal());
        $this->assertInstanceOf(Hal::class, $hal);
    }

    public function testHalLinker(): void
    {
        $halRenderer = new HalRenderer(new AnnotationReader(), new HalLinker(new FakeReverseLinker()));
        $fakeRo = new FakeNullRo();
        $fakeRo->uri = new Uri('app://self/?id=10');
        $fakeRo->setRenderer($halRenderer);
        $fakeRo->headers = ['Location' => 'http://example.com/go?id=10'];
        (string) $fakeRo; // @phpstan-ignore-line
        $this->assertSame('/user/10', $fakeRo->headers['Location']);
    }
}

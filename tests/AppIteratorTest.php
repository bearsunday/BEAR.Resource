<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceDirException;
use PHPUnit\Framework\TestCase;

class AppIteratorTest extends TestCase
{
    /**
     * @var AppIterator
     */
    private $appIterator;

    protected function setUp() : void
    {
        $resourceDir = __DIR__ . '/Fake/Mock';
        $this->appIterator = new AppIterator($resourceDir);
    }

    public function testForEach() : void
    {
        foreach ($this->appIterator as $key => $meta) {
            $isValidUri = filter_var($meta->uri, FILTER_VALIDATE_URL);
            $this->assertTrue((bool) $isValidUri);
            $this->assertInstanceOf(Meta::class, $meta);
        }
    }

    public function testResourceDirException() : void
    {
        $this->expectException(ResourceDirException::class);
        new AppIterator('/invalid');
    }
}

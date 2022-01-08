<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\ImportApp;
use BEAR\Resource\ResourceInterface;
use FakeVendor\Sandbox\Module\AppModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function dirname;
use function file_put_contents;
use function glob;
use function is_dir;
use function rmdir;
use function unlink;

class ImportModuleTest extends TestCase
{
    protected function setUp(): void
    {
        $rm = static function ($dir) use (&$rm) {
            foreach ((array) glob($dir . '/*') as $f) {
                $file = (string) $f;
                is_dir($file) ? $rm($file) : unlink($file);
                @rmdir($file);
            }
        };
        $tmpDir = dirname(__DIR__, 2) . '/tests/Fake/FakeVendor/Blog/var/tmp';
        $rm($tmpDir);
        file_put_contents($tmpDir . '/tmp.text', '1');
        parent::setUp();
    }

    public function testConfigure(): void
    {
        $module = new AppModule();
        $importConfig = [new ImportApp('blog', 'FakeVendor\Blog', 'app')];
        $module->override(new ImportAppModule($importConfig));
        $resource = (new Injector($module, __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        // request
        $news = $resource
            ->get
            ->uri('app://self/news')
            ->withQuery(['date' => 'today'])
            ->request();
        $expect = '{
    "weather": {
        "today": "the weather of today is sunny"
    },
    "headline": "40th anniversary of Rubik\'s Cube invention.",
    "sports": "Pieter Weening wins Giro d\'Italia.",
    "user": {
        "id": 2,
        "name": "Aramis",
        "age": 16,
        "blog_id": 12
    }
}
';
        $this->assertSame($expect, (string) $news);

        $news = $resource
            ->get
            ->uri('app://blog/news')
            ->withQuery(['date' => 'today'])
            ->request();
        $expect = '{
    "weather": {
        "today": "the weather of today is sunny"
    },
    "technology": "Microsoft to stop producing Windows versions",
    "user": {
        "id": 3,
        "name": "Porthos",
        "age": 17,
        "blog_id": 0
    }
}
';
        $this->assertSame($expect, (string) $news);
    }
}

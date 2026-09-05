<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function basename;
use function dirname;
use function file_get_contents;
use function preg_match;
use function preg_quote;
use function sprintf;

final class DeprecatedIsolationTest extends TestCase
{
    /**
     * composer.json excludes src-deprecated from the classmap, so an optimized autoloader
     * cannot load it. A live binding on one of those classes fails composition under
     * `composer dump-autoload --classmap-authoritative`.
     */
    public function testLiveCodeNamesNoDeprecatedClass(): void
    {
        $root = dirname(__DIR__);
        $leaked = [];
        foreach ($this->phpFiles($root . '/src') as $file) {
            $source = (string) file_get_contents($file->getPathname());
            foreach ($this->phpFiles($root . '/src-deprecated') as $deprecated) {
                $class = basename($deprecated->getFilename(), '.php');
                if (! preg_match(sprintf('/\b%s::class\b/', preg_quote($class, '/')), $source)) {
                    continue;
                }

                $leaked[] = sprintf('%s names %s', $file->getFilename(), $class);
            }
        }

        $this->assertSame([], $leaked);
    }

    /** @return iterable<SplFileInfo> */
    private function phpFiles(string $dir): iterable
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            yield $file;
        }
    }
}

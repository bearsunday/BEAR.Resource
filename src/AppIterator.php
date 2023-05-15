<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceDirException;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

use function array_diff_key;
use function array_key_exists;
use function array_keys;
use function array_values;
use function assert;
use function class_exists;
use function file_exists;
use function get_declared_classes;
use function str_contains;

/** @implements Iterator<string, Meta> */
final class AppIterator implements Iterator
{
    private int $i = 0;

    /** @var array<string, Meta> */
    private array $metaCollection = [];

    /** @var list<string> */
    private array $keys = [];

    /** @throws ResourceDirException */
    public function __construct(string $resourceDir)
    {
        if (! file_exists($resourceDir)) {
            throw new ResourceDirException($resourceDir);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($resourceDir),
            RecursiveIteratorIterator::SELF_FIRST,
        );
        $this->metaCollection = $this->getMetaCollection($iterator);
        $this->keys = array_keys($this->metaCollection);
    }

    /**
     * {@inheritDoc}
     */
    public function current(): Meta
    {
        return $this->metaCollection[$this->keys[$this->i]];
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        ++$this->i;
    }

    /**
     * {@inheritDoc}
     */
    public function key(): string
    {
        return $this->keys[$this->i];
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return array_key_exists($this->i, $this->keys);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->i = 0;
    }

    /**
     * @param Iterator<SplFileInfo> $iterator
     *
     * @return array<string, Meta>
     */
    private function getMetaCollection(Iterator $iterator): array
    {
        $metaCollection = [];
        foreach ($iterator as $item) {
            if ($this->isNotPhp($item)) {
                continue;
            }

            $resourceClass = $this->getResourceClassName($item);
            if ($resourceClass === '') {
                continue;
            }

            assert(class_exists($resourceClass));
            $meta = new Meta($resourceClass);
            $metaCollection[$meta->uri] = $meta;
        }

        return $metaCollection;
    }

    private function isNotPhp(SplFileInfo $item): bool
    {
        $isPhp = $item->isFile()
            && $item->getExtension() === 'php'
            && (! str_contains($item->getBasename('.php'), '.'));

        return ! $isPhp;
    }

    private function getResourceClassName(SplFileInfo $file): string
    {
        $pathName = $file->getPathname();
        $declaredClasses = get_declared_classes();
        assert(file_exists($pathName));
        include_once $pathName;
        $newClasses = array_values(array_diff_key(get_declared_classes(), $declaredClasses));

        return $this->getName($newClasses);
    }

    /**
     * @param array<class-string> $newClasses
     *
     * @return class-string|string
     */
    private function getName(array $newClasses): string
    {
        foreach ($newClasses as $newClass) {
            $parent = (new ReflectionClass($newClass))->getParentClass();
            if ($parent && $parent->name === ResourceObject::class) {
                return $newClass;
            }
        }

        return '';
    }
}

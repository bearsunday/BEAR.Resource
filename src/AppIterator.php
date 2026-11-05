<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceDirException;
use Iterator;
use Override;
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

/**
 * @psalm-import-type ResourceClassName from Types
 * @psalm-import-type MetaMap from Types
 * @psalm-import-type ClassNameList from Types
 * @implements Iterator<string, Meta>
 */
final class AppIterator implements Iterator
{
    private int $i = 0;

    /** @var MetaMap */
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
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function current(): Meta
    {
        return $this->metaCollection[$this->keys[$this->i]];
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-refs
     */
    #[Override]
    public function next(): void
    {
        ++$this->i;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function key(): string
    {
        return $this->keys[$this->i]; // @codeCoverageIgnore
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function valid(): bool
    {
        return array_key_exists($this->i, $this->keys);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-refs
     */
    #[Override]
    public function rewind(): void
    {
        $this->i = 0;
    }

    /**
     * @param Iterator<SplFileInfo> $iterator
     *
     * @return MetaMap
     */
    private function getMetaCollection(Iterator $iterator): array
    {
        $metaCollection = [];
        foreach ($iterator as $item) {
            if ($this->isNotPhp($item)) {
                continue;
            }

            $resourceClass = $this->getResourceClassName($item);
            if ($resourceClass === '' || ! class_exists($resourceClass)) {
                continue;
            }

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

    /** @return ResourceClassName|'' */
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
     * @param ClassNameList $newClasses
     *
     * @return ResourceClassName|''
     *
     * @psalm-capabilities read-props
     */
    private function getName(array $newClasses): string
    {
        foreach ($newClasses as $newClass) {
            $parent = (new ReflectionClass($newClass))->getParentClass();
            if ($parent && $parent->name === ResourceObject::class) {
                /** @var ResourceClassName $newClass */
                return $newClass;
            }
        }

        return '';
    }
}

<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceDirException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class AppIterator implements \Iterator
{
    /**
     * @var int
     */
    private $i = 0;

    /**
     * @var array
     */
    private $metaCollection = [];

    /**
     * @var array
     */
    private $keys = [];

    /**
     * @throws ResourceDirException
     */
    public function __construct(string $resourceDir)
    {
        if (! file_exists($resourceDir)) {
            throw new ResourceDirException($resourceDir);
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($resourceDir),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $this->metaCollection = $this->getMetaCollection($iterator);
        $this->keys = array_keys($this->metaCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->metaCollection[$this->keys[$this->i]];
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        ++$this->i;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->keys[$this->i];
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return array_key_exists($this->i, $this->keys);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->i = 0;
    }

    private function getMetaCollection(\Iterator $iterator) : array
    {
        $metaCollection = [];
        foreach ($iterator as $item) {
            /* @var $item \SplFileInfo */
            if ($this->isNotPhp($item)) {
                continue;
            }
            $resourceClass = $this->getResourceClassName($item);
            if ($resourceClass === '') {
                continue;
            }
            $meta = new Meta($resourceClass);
            $metaCollection[$meta->uri] = $meta;
        }

        return $metaCollection;
    }

    private function isNotPhp(\SplFileInfo $item) : bool
    {
        $isPhp = $item->isFile()
            && $item->getExtension() === 'php'
            && (strpos($item->getBasename('.php'), '.') === false);

        return ! $isPhp;
    }

    private function getResourceClassName(\SplFileInfo $file) : string
    {
        $pathName = $file->getPathname();
        $declaredClasses = get_declared_classes();
        /** @noinspection PhpIncludeInspection */
        include_once $pathName;
        $newClasses = array_values(array_diff_key(get_declared_classes(), $declaredClasses));
        $name = $this->getName($newClasses);

        return $name;
    }

    private function getName(array $newClasses) : string
    {
        foreach ($newClasses as $newClass) {
            $parent = (new \ReflectionClass($newClass))->getParentClass();
            if ($parent && $parent->name === ResourceObject::class) {
                return $newClass;
            }
        }

        return '';
    }
}

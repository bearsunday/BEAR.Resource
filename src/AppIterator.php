<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @param string $resourceDir
     */
    public function __construct($resourceDir)
    {
        if (!file_exists($resourceDir)) {
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
        return isset($this->keys[$this->i]);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->i = 0;
    }

    /**
     * @param \Iterator $iterator
     *
     * @return array
     */
    private function getMetaCollection(\Iterator $iterator)
    {
        $metaCollection = [];
        foreach ($iterator as $item) {
            /** @var $item \SplFileInfo */
            $isPhp = $item->isFile()
                && $item->getExtension() === 'php'
                && (strpos($item->getBasename('.php'), '.') === false);
            if (!$isPhp) {
                continue;
            }
            $resourceClass = $this->getResourceClassName($item);
            if ($resourceClass === false) {
                continue;
            }
            $meta = new Meta($resourceClass);
            $metaCollection[$meta->uri] = $meta;
        }

        return $metaCollection;
    }

    /**
     * @param \SplFileInfo $file
     *
     * @return string | false
     */
    private function getResourceClassName(\SplFileInfo $file)
    {
        static $cache = [];

        $pathName = $file->getPathname();
        if (isset($cache[$pathName])) {
            return $cache[$pathName];
        }
        $declaredClasses = get_declared_classes();
        include_once $pathName;
        $newClasses = array_values(array_diff_key(get_declared_classes(), $declaredClasses));
        foreach ($newClasses as $newClass) {
            $parent = (new \ReflectionClass($newClass))->getParentClass();
            if ($parent && $parent->name === ResourceObject::class) {
                $cache[$pathName] = $newClass;

                return $newClass;
            }
        }

        return false;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource;

use ArrayAccess;
use BEAR\Resource\Exception\LinkException;
use LogicException;

/**
 * The resource's links by rel, read-only, as `HalLinker::link()` returns them
 *
 * Every rel reports as set, so reading a rel the resource does not link throws LinkException instead of reading as empty.
 *
 * @implements ArrayAccess<string, array{href: string, title: string}>
 */
final readonly class ResourceLinks implements ArrayAccess
{
    public function __construct(
        private ResourceObject $ro,
        private HalLinker $linker,
    ) {
    }

    /** @param string $offset */
    public function offsetExists(mixed $offset): bool
    {
        return true;
    }

    /**
     * @param string $offset
     *
     * @return array{href: string, title: string}
     *
     * @throws LinkException
     */
    public function offsetGet(mixed $offset): array
    {
        return $this->linker->link($this->ro, $offset);
    }

    /**
     * @param string                             $offset
     * @param array{href: string, title: string} $value
     */
    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new LogicException(__METHOD__ . ' not supported');
    }

    /** @param string $offset */
    public function offsetUnset(mixed $offset): never
    {
        throw new LogicException(__METHOD__ . ' not supported');
    }
}

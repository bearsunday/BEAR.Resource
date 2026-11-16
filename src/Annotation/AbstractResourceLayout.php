<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

/** Base of a class attribute declaring the resource's layout; a resource has at most one. */
abstract class AbstractResourceLayout
{
    /**
     * @return array<string, string> rel => URI template, expanded with the onGet() arguments
     *
     * @psalm-mutation-free
     */
    abstract public function embed(): array;
}

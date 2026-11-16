<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

/** Base of a class attribute declaring the resource's layout; each layout declares its own EMBED. */
abstract class AbstractResourceLayout
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ClassConstantTypeHint, SlevomatCodingStandard.TypeHints.UselessConstantTypeHint
    /** @var array<string, string> rel => URI template, expanded with the onGet() arguments */
    public const EMBED = [];
}

<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use BEAR\Resource\Types;

/**
 * Base of a class attribute declaring the resource's layout; each layout declares its own EMBED.
 *
 * @psalm-import-type LayoutEmbedMap from Types
 */
abstract class AbstractResourceLayout
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ClassConstantTypeHint, SlevomatCodingStandard.TypeHints.UselessConstantTypeHint
    /** @var LayoutEmbedMap rel => URI template, expanded with the onGet() arguments */
    public const EMBED = [];
}

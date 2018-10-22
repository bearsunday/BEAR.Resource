<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class Embed
{
    /**
     * Relation
     *
     * @var string
     */
    public $rel;

    /**
     * Embed resource uri
     *
     * @var string
     */
    public $src;
}

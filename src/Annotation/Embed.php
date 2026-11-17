<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Embed
{
    /**
     * @param string $rel Body key that holds the embedded request; `_self` merges the embedded body into this one instead
     * @param string $src URI template expanded with the method arguments; a path starting with `/` keeps this resource's scheme and host
     *
     * @psalm-pure
     */
    public function __construct(
        public string $rel = '',
        public string $src = '',
    ) {
    }
}

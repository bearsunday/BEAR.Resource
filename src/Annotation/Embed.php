<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

/**
 * Puts a request for another resource in the body before the method runs
 *
 * @see https://bearsunday.github.io/manuals/1.0/en/resource_link.html#internal-links
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Embed
{
    /**
     * @param string $rel Body key that holds the embedded request; `_self` merges the embedded body into this body
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

<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Embed
{
    /** @psalm-pure */
    public function __construct(
        public string $rel = '',
        public string $src = '',
    ) {
    }
}

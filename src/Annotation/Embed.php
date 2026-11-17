<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
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

    /** @psalm-pure */
    public function __construct(string $rel = '', string $src = '')
    {
        $this->rel = $rel;
        $this->src = $src;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

/** @psalm-immutable */
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

    /**
     * @param array{rel?: string, src?: string} $values
     *
     * @psalm-capabilities read-props
     */
    public function __construct(array $values = [], string $rel = '', string $src = '')
    {
        $this->rel = $values['rel'] ?? $rel;
        $this->src = $values['src'] ?? $src;
    }
}

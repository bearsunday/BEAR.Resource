<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD)]
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
     */
    public function __construct(array $values = [], string $rel = '', string $src = '')
    {
        $this->rel = $values['rel'] ?? $rel;
        $this->src = $values['src'] ?? $src;
    }
}

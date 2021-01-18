<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Embed implements NamedArgumentConstructorAnnotation
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

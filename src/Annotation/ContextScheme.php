<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;
use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
#[Attribute(Attribute::TARGET_METHOD), Qualifier]
final class ContextScheme implements NamedArgumentConstructorAnnotation
{
    /** @var string */
    public $value;

    /** @var string $value */
    public function __construct($value = '')
    {
        $this->value = $value;
    }
}

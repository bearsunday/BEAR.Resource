<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Doctrine\Common\Annotations\NamedArgumentConstructorAnnotation;
use Ray\Di\Di\Qualifier;
use function is_string;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
#[Attribute(Attribute::TARGET_METHOD|Attribute::TARGET_PARAMETER), Qualifier]
final class AppName implements NamedArgumentConstructorAnnotation
{
    /** @var string */
    public $value;

    /** @var string $value */
    public function __construct($value)
    {
        $this->value = $value;
    }
}

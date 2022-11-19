<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 * @NamedArgumentConstructor
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER), Qualifier]
final class ContextScheme
{
    public function __construct(public string $value = '')
    {
    }
}

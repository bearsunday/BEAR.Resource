<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Ray\Di\Di\Qualifier;

/**
 * @Annotation
 * @Target("METHOD")
 * @Qualifier
 */
final class ContextSchema
{
    public $value;
}

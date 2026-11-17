<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Qualifies the scheme and host that UriFactory puts before a URI with no scheme
 *
 * @psalm-pure
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class ContextScheme
{
}

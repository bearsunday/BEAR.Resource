<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;

/**
 * Input parameter entity attribute
 *
 * This attribute marks parameters that should be automatically
 * constructed from HTTP input parameters as domain objects.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Input
{
}

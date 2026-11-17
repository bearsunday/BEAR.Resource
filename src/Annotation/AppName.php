<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Qualifies the application namespace that the `app://self` and `page://self` resources live in
 *
 * @psalm-pure
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class AppName
{
}

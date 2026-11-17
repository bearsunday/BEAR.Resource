<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

use Attribute;
use Ray\Di\Di\Qualifier;

/**
 * Qualifies the ImportApp list that maps each imported host to its application
 *
 * @psalm-pure
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class ImportAppConfig
{
}

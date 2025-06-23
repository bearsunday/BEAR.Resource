<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Scalar;

use Attribute;
use Ray\Di\Di\Qualifier;

#[Attribute(Attribute::TARGET_PARAMETER)]
#[Qualifier]
final class OtherQualifier
{
}

<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Attribute;

/**
 * @Annotation
 * @Target("METHOD")
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class FakeNull
{
}

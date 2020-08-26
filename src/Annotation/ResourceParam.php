<?php

declare(strict_types=1);

namespace BEAR\Resource\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class ResourceParam
{
    /** @var string */
    public $param;

    /** @var string */
    public $uri;

    /** @var bool */
    public $templated = false;
}

<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
final class ResourceParam
{
    /**
     * @var string
     */
    public $param;

    /**
     * @var string
     */
    public $uri;

    /**
     * @var bool
     */
    public $templated = false;
}

<?php
/**
 * This file is part of the BEAR.Sunday package.
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
}

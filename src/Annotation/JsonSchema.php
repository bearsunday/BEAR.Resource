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
final class JsonSchema
{
    /**
     * Json schema body key name
     *
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $schema;

    /**
     * Input parameter validation schema
     *
     * @var string
     */
    public $params;
}

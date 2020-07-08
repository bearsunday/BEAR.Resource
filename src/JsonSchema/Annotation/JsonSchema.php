<?php

declare(strict_types=1);

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
    public $key = '';

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

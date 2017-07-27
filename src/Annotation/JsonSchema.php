<?php
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
     * Input parameter validation scheme
     *
     * @var string
     */
    public $request;
}

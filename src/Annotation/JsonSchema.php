<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Annotation;

use Doctrine\Common\Annotations\Annotation\Enum;

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
     * @Enum({"none", "request", "response", "request_response"})
     */
    public $validate = "response";
}

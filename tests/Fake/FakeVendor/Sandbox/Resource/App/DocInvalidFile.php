<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class DocInvalidFile extends ResourceObject
{
    /**
     * @JsonSchema(schema="__NOT_FOUND_.json")
     */
    public function onGet($id)
    {
        return $this;
    }
}

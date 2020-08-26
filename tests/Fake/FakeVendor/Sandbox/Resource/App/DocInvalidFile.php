<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class DocInvalidFile extends ResourceObject
{
    /**
     * @JsonSchema(schema="__NOT_FOUND_.json")
     */
    public function onGet(int $id)
    {
        return $this;
    }
}

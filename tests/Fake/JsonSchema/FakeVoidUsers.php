<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class FakeVoidUsers extends ResourceObject
{
    /**
     * @JsonSchema(schema="users.json")
     */
    public function onGet(int $age)
    {
        return $this;
    }
}

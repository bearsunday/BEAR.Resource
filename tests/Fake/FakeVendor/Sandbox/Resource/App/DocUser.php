<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class DocUser extends ResourceObject
{
    /**
     * User
     *
     * Returns a variety of information about the user specified by the required $id parameter
     *
     * @param string $id User ID
     *
     * @JsonSchema(schema="user.json")
     */
    public function onGet(string $id)
    {
        return $this;
    }
}

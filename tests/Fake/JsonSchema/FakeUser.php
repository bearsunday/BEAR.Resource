<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class FakeUser extends ResourceObject
{
    /**
     * @JsonSchema(schema="user.json", request="user.get.json")
     */
    public function onGet($id)
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => 12
        ];

        return $this;
    }

    /**
     * @JsonSchema(schema="__invalid.json")
     */
    public function onPost()
    {
        return $this;
    }
}

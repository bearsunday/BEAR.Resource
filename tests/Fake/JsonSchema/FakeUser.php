<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class FakeUser extends ResourceObject
{
    /**
     * @JsonSchema(schema="user.json", request="user.get.json")
     */
    public function onGet($age)
    {
        $this->body = [
            'firstName' => 'mucha',
            'lastName' => 'alfons',
            'age' => $age
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

    /**
     * @JsonSchema(schema="user.json")
     */
    public function onPut()
    {
        $this->code = Code::NO_CONTENT;
        $this->body = [];

        return $this;
    }

    /**
     * @JsonSchema(request="__invalid.json")
     */
    public function onPatch()
    {
        return $this;
    }
}

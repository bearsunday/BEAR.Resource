<?php

declare(strict_types=1);
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
     * @JsonSchema(schema="user.json", params="user.get.json")
     * {@SuppressWarnings("unused")}
     */
    public function onGet($age, $gender = 'male')
    {
        // in practical codes, an argument $gender may be consumed internally.
        $name = [
            'firstName' => 'mucha',
            'lastName' => 'alfons'
        ];
        $this->body = [
            'name' => $name,
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
     * @JsonSchema(schema="definitions/user.json")
     */
    public function onPut()
    {
        $this->code = Code::NO_CONTENT;
        $this->body = [];

        return $this;
    }

    /**
     * @JsonSchema(params="__invalid.json")
     */
    public function onPatch()
    {
        return $this;
    }
}

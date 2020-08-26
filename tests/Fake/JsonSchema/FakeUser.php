<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use ArrayIterator;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class FakeUser extends ResourceObject
{
    /**
     * @JsonSchema(schema="user.json", params="user.get.json")
     * {@SuppressWarnings("unused")}
     */
    public function onGet(int $age, string $gender = 'male')
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

    /**
     * @JsonSchema(key="__invalid__", schema="user.json")
     */
    public function invalidKey()
    {
        return $this;
    }
}

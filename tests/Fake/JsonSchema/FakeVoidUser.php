<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class FakeVoidUser extends ResourceObject
{
    /**
     * @JsonSchema(schema="user.json", params="user.get.json")
     * {@SuppressWarnings("unused")}
     */
    public function onGet(int $age, string $gender = 'male')
    {
        unset($age);
        unset($gender);

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

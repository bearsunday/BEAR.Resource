<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class FakeUsers extends ResourceObject
{
    /**
     * @JsonSchema(schema="users.json")
     */
    public function onGet(int $age)
    {
        $name = [
            'firstName' => 'mucha',
            'lastName' => 'alfons'
        ];
        $user = [
            'name' => $name,
            'age' => $age
        ];
        $this->body = [
            $user,
            $user,
            $user
        ];

        return $this;
    }
}

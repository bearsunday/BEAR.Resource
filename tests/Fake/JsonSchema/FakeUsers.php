<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class FakeUsers extends ResourceObject
{
    /**
     * @JsonSchema(schema="users.json")
     */
    public function onGet($age)
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

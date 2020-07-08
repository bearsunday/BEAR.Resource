<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\ResourceObject;

class FakeSnake extends ResourceObject
{
    public function onGet(int $age, string $gender = 'male')
    {
        $name = [
            'first_name' => 'mucha',
            'last_name' => 'alfons'
        ];
        $this->body = [
            'name' => $name,
            'age' => $age
        ];

        return $this;
    }
}

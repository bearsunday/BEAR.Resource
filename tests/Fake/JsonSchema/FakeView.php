<?php

declare(strict_types=1);

namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class FakeView extends ResourceObject
{
    /**
     * {@SuppressWarnings("unused")}
     */
    #[JsonSchema(schema: 'user.json', target: 'view')]
    public function onGet(int $age, string $gender = 'male')
    {
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
}

<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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

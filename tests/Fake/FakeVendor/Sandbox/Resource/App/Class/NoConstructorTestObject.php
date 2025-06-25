<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\ResourceObject;

class NoConstructorTestObject extends ResourceObject
{
    public function onGet(PersonWithoutConstructor $person)
    {
        $this->body = [
            'name' => $person->name,
            'age' => $person->age,
        ];

        return $this;
    }
}
<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\ResourceObject;

class DefaultValueTestObject extends ResourceObject
{
    public function onGet(PersonWithDefaultValue $person)
    {
        $this->body = [
            'name' => $person->name,
            'age' => $person->age,
        ];

        return $this;
    }
}
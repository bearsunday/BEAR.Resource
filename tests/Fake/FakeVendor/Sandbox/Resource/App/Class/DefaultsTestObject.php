<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\ResourceObject;

class DefaultsTestObject extends ResourceObject
{
    public function onGet(PersonWithDefaults $person)
    {
        $this->body = [
            'name' => $person->name,
            'title' => $person->title,
        ];

        return $this;
    }
}
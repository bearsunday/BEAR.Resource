<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Input;
use FakeVendor\Sandbox\Resource\App\Class\SimpleTestObject;

class TestResourceWithDefaults extends ResourceObject
{
    public function onGet(SimpleTestObject $person): static
    {
        $this->body = [
            'name' => $person->name,
            'age' => $person->age,
        ];

        return $this;
    }

    public function onPost(#[Input(key: 'user')]
    SimpleTestObject $person,): static
    {
        $this->body = [
            'name' => $person->name,
            'age' => $person->age,
        ];

        return $this;
    }
}

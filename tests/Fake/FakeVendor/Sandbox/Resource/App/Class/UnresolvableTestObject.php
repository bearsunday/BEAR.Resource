<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\ResourceObject;

class UnresolvableTestObject extends ResourceObject
{
    public function onGet(PersonUnresolvable $person)
    {
        $this->body = [
            'name' => $person->name,
            'service' => $person->service->serve(),
        ];

        return $this;
    }
}
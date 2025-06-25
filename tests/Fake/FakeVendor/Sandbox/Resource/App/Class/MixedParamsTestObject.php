<?php

declare(strict_types=1);

namespace BEAR\Resource\FakeVendor\Sandbox\Resource\App\Class;

use BEAR\Resource\ResourceObject;

class MixedParamsTestObject extends ResourceObject
{
    public function onGet(PersonWithMixedParams $person)
    {
        $this->body = [
            'name' => $person->name,
            'title' => $person->title,
            'country' => $person->country,
        ];

        return $this;
    }
}
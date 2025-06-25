<?php

declare(strict_types=1);

namespace BEAR\Resource;

use FakeVendor\Sandbox\Resource\App\Class\NoConstructorObject;

class TestResourceWithoutConstructor extends ResourceObject
{
    public function onGet(NoConstructorObject $person): static
    {
        $this->body = [
            'name' => $person->name,
            'age' => $person->age,
        ];

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource;

class TestResourceEnum extends ResourceObject
{
    public function onGet(FakeStringBacked $status): static
    {
        $this->body = [
            'status' => $status->value,
        ];

        return $this;
    }
}

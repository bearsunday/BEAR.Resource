<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Carry;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class Stop extends ResourceObject
{
    public function onGet(): static
    {
        $this->code = Code::NOT_FOUND;
        $this->headers = ['Surrogate-Key' => 'stop'];
        $this->body = ['name' => 'stop'];

        return $this;
    }
}

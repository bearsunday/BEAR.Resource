<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Jump;

use BEAR\Resource\ResourceObject;

class Second extends ResourceObject
{
    public function onGet(int $id): static
    {
        $this->headers = ['ETag' => 'second'];
        $this->body = ['id' => $id, 'name' => 'second'];
        $this->view = 'second view';

        return $this;
    }
}

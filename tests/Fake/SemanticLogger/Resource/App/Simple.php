<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\ResourceObject;

final class Simple extends ResourceObject
{
    public function onGet(string $id = 'default'): self
    {
        $this->code = 200;
        $this->body = [
            'id' => $id,
            'message' => 'Hello from Simple',
            'timestamp' => time(),
        ];

        return $this;
    }

    public function onPost(string $name, string $email = ''): self
    {
        $this->code = 201;
        $this->body = [
            'name' => $name,
            'email' => $email,
            'status' => 'created',
        ];

        return $this;
    }
}
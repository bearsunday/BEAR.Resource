<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\ResourceObject;

final class Nested extends ResourceObject
{
    public function onGet(string $type = 'info'): self
    {
        $this->code = 200;
        $this->body = [
            'type' => $type,
            'data' => 'Nested resource data',
            'level' => 'nested',
        ];

        return $this;
    }
}
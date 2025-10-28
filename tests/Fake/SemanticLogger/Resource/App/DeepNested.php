<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\ResourceObject;

final class DeepNested extends ResourceObject
{
    public function onGet(string $level = 'deep'): self
    {
        $this->code = 200;
        $this->body = [
            'level' => $level,
            'depth' => 'deep_nested',
            'data' => 'Third level resource data',
        ];

        return $this;
    }
}
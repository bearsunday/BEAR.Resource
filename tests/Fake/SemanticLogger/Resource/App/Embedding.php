<?php

declare(strict_types=1);

namespace BEAR\Resource\Fake\SemanticLogger\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

final class Embedding extends ResourceObject
{
    #[Embed(rel: 'simple', src: 'app://self/simple{?id}')]
    #[Embed(rel: 'nested', src: 'app://self/nested{?type}')]
    public function onGet(string $id = 'embed_test', string $type = 'embedded'): self
    {
        $this->code = 200;
        $this->body = [
            'main_id' => $id,
            'main_type' => $type,
            'description' => 'Resource with embedded content',
        ];

        return $this;
    }
}
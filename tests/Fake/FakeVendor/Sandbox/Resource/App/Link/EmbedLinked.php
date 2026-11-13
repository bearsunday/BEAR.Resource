<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class EmbedLinked extends ResourceObject
{
    #[Embed(rel: 'blog', src: 'app://self/author{?id}#link=self:blog')]
    public function onGet(int $id): static
    {
        unset($id);

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page\EmbedPage;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\FakeContentPage;
use BEAR\Resource\ResourceObject;

#[FakeContentPage]
class Both extends ResourceObject
{
    #[Embed(rel: 'bird', src: 'app://self/bird/sparrow{?id}')]
    #[Embed(rel: 'footer', src: 'app://self/bird/canary')]
    public function onGet(string $id, string $lang = 'ja'): static
    {
        return $this;
    }
}

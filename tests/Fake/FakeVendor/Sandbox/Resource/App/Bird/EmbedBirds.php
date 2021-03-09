<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class EmbedBirds extends ResourceObject
{
    /**
     * @Embed(rel="birds", src="app://self/bird/birds{?id}")
     */
    #[Embed(rel: "birds", src: "app://self/bird/birds{?id}")]
    public function onGet(string $id)
    {
        return $this;
    }
}

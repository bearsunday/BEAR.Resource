<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Location;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class EmbedUnsafe extends ResourceObject
{
    #[Embed(rel: 'item', src: 'app://self/link/location/form#link=self:create?name=embedded')]
    public function onGet(): static
    {
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Location;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class EmbedUnsafe extends ResourceObject
{
    #[Embed(rel: 'item', src: 'app://self/link/location/guarded#link=self:doTouch')]
    public function onGet(): static
    {
        return $this;
    }
}

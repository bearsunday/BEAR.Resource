<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Location;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use LogicException;

class Guarded extends ResourceObject
{
    #[Link(rel: 'doTouch', href: 'app://self/link/location/guarded', method: 'post')]
    public function onGet(): static
    {
        $this->body = [];

        return $this;
    }

    public function onPost(): static
    {
        throw new LogicException('An embed must not request a POST link');
    }
}

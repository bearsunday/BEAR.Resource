<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Location;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Form extends ResourceObject
{
    #[Link(rel: 'create', href: 'app://self/link/location/item', method: 'post')]
    #[Link(rel: 'update', href: 'app://self/link/location/item{?id}', method: 'put')]
    #[Link(rel: 'redirectTwice', href: 'app://self/link/location/redirect', method: 'post')]
    #[Link(rel: 'leave', href: 'app://self/link/location/redirect', method: 'put')]
    #[Link(rel: 'noLocation', href: 'app://self/link/location/redirect', method: 'delete')]
    public function onGet(): static
    {
        $this->body = ['id' => 7];

        return $this;
    }
}

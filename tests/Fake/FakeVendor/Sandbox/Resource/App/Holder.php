<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Holder extends ResourceObject
{
    public function __construct(ResourceInterface $resource)
    {
        $resource->get->uri('app://self/author?id=1')->eager->request();
    }

    public function onPost()
    {
        return true;
    }
}

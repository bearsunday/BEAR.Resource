<?php

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\UnboundInterface;

class Unbound extends ResourceObject
{
    public function __construct(UnboundInterface $missing)
    {
    }
}

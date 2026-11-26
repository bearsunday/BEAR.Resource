<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Location;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class NetworkPath extends ResourceObject
{
    public function onPost(): static
    {
        $this->code = Code::SEE_OTHER;
        $this->headers['Location'] = '//example.com/item';

        return $this;
    }
}

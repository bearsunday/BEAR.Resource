<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Location;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class Redirect extends ResourceObject
{
    public function onGet(): static
    {
        $this->code = Code::SEE_OTHER;
        $this->headers['Location'] = '/link/location/redirect';

        return $this;
    }

    public function onPost(): static
    {
        $this->code = Code::SEE_OTHER;
        $this->headers['Location'] = '/link/location/redirect';

        return $this;
    }

    public function onPut(): static
    {
        $this->code = Code::SEE_OTHER;
        $this->headers['Location'] = 'https://example.com/';

        return $this;
    }

    public function onDelete(): static
    {
        $this->code = Code::SEE_OTHER;

        return $this;
    }

    public function onPatch(): static
    {
        $this->code = Code::SEE_OTHER;
        $this->headers['location'] = '/link/location/item?id=4';

        return $this;
    }
}

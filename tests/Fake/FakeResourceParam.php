<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeResourceParam implements AdapterInterface
{
    public function get(AbstractUri $uri): ResourceObject
    {
        unset($uri);

        return new FakeNopResource();
    }
}

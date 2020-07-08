<?php

declare(strict_types=1);

namespace BEAR\Resource;

class FakeProv implements AdapterInterface
{
    public function get(AbstractUri $uri): ResourceObject
    {
        unset($uri);

        return new NullResourceObject();
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource\Mock;

use BEAR\Resource\Annotation\Provides;
use BEAR\Resource\ResourceObject;

class Comment extends ResourceObject
{
    public function onGet(int $id)
    {
        return "entry {$id}";
    }

    public function provideId()
    {
        return ['aaa' => 1];
    }
}

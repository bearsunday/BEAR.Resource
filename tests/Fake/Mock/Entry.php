<?php

declare(strict_types=1);

namespace BEAR\Resource\Mock;

use BEAR\Resource\ResourceObject;

class Entry extends ResourceObject
{
    public function onGet(int $id)
    {
        return "entry {$id}";
    }

    /**
     * @Provides
     */
    public function provideId()
    {
        return ['id' => 1];
    }
}

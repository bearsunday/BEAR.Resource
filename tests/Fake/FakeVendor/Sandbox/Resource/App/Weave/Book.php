<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Weave;

use BEAR\Resource\Annotation\FakeLog;
use BEAR\Resource\ResourceObject;

class Book extends ResourceObject
{
    /**
     * @FakeLog
     */
    #[FakeLog]
    public function onGet(int $id)
    {
        return "book id[{$id}]";
    }
}

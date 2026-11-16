<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page\Layout;

use BEAR\Resource\ResourceObject;

class Footer extends ResourceObject
{
    public static int $count = 0;

    public function onGet(string $id, string $lang): static
    {
        self::$count++;
        $this->body = ['id' => $id, 'lang' => $lang];

        return $this;
    }
}

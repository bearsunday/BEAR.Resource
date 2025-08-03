<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\ResourceObject;

class Event extends ResourceObject
{
    /** @var array{event: string} */
    public $body;

    public function onGet(string $newsDate): self
    {
        $this->body = ['event' => $newsDate];

        return $this;
    }
}

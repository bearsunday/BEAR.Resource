<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\ResourceObject;

class Weather extends ResourceObject
{
    /** @var array{today: string} */
    public $body;
    public function onGet($date): self
    {
        $this->body['today'] = "the weather of {$date} is sunny";

        return $this;
    }
}

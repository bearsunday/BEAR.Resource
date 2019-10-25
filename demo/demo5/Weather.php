<?php

declare(strict_types=1);

namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\ResourceObject;

class Weather extends ResourceObject
{
    public function onGet(string $date) : ResourceObject
    {
        $this->body = [
            'today' => "the weather of {$date} is sunny"
        ];

        return $this;
    }
}

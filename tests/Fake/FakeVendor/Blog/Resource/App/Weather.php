<?php

declare(strict_types=1);

namespace FakeVendor\Blog\Resource\App;

use BEAR\Resource\ResourceObject;

class Weather extends ResourceObject
{
    public $links = [
        'tomorrow' => 'app://self/weather/tomorrow'
    ];

    public function onGet($date)
    {
        $this['today'] = "the weather of {$date} is sunny";

        return $this;
    }
}

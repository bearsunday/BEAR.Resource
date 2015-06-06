<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Weather extends ResourceObject
{
    /**
     * @param string $date
     *
     * @Link(rel="tomorrow", href="/weather/tomorrow")
     */
    public function onGet($date)
    {
        $this['today'] = "the weather of {$date} is sunny";

        return $this;
    }
}

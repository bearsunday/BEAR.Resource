<?php

namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Annotation\Embed;

class News extends ResourceObject
{
    /**
     * @Embed(rel="weather",src="app://self/weather{?date}")
     */
    public function onGet($date)
    {
        $this['headline'] = "40th anniversary of Rubik's Cube invention.";
        $this['sports'] = "Pieter Weening wins Giro d'Italia.";
        
        return $this;
    }
}

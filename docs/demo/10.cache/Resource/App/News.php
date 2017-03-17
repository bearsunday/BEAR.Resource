<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyApp\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

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

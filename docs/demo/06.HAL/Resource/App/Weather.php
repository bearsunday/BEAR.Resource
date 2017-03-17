<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\MyApp\Resource\App;

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

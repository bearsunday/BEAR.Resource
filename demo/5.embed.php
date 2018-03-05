<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

class News extends ResourceObject
{
    /**
     * @Embed(rel="weather", src="app://self/weather{?date}")
     */
    public function onGet($date)
    {
        $this->body = [
            'headline' => "40th anniversary of Rubik's Cube invention.",
            'sports' => "Pieter Weening wins Giro d'Italia."
        ];

        return $this;
    }
}

class Weather extends ResourceObject
{
    public function onGet($date)
    {
        $this->body = [
            'today' => "the weather of {$date} is sunny"
        ];

        return $this;
    }
}

/** @var ResourceInterface $resource */
$resource = (new Injector(new HalModule(new ResourceModule('MyVendor\Sandbox'))))->getInstance(ResourceInterface::class);
// request
$news = $resource->get->uri('app://self/news')(['date' => 'today']);
echo $news . PHP_EOL;
//{
//    "headline": "40th anniversary of Rubik's Cube invention.",
//    "sports": "Pieter Weening wins Giro d'Italia.",
//    "_embedded": {
//    "weather": {
//        "today": "the weather of today is sunny",
//            "_links": {
//            "self": {
//                "href": "/weather?date=today"
//                }
//            }
//        }
//    },
//    "_links": {
//    "self": {
//        "href": "/news?date=today"
//        }
//    }
//}

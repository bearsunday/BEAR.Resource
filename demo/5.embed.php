<?php

declare(strict_types=1);

namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';
require __DIR__ . '/demo5/News.php';
require __DIR__ . '/demo5/Weather.php';

/* @var ResourceInterface $resource */
$resource = (new Injector(new HalModule(new ResourceModule('MyVendor\Demo')), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
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

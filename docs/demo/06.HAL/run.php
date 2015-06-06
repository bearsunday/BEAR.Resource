<?php

use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Injector;

bootstarp: {
    $loader = require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
    /* @var $loader \Composer\Autoload\ClassLoader */
    $loader->addPsr4('MyVendor\\MyApp\\', __DIR__);
}

main: {
    // create resource client with HalModule
    $resource = (new Injector(new HalModule(new ResourceModule('MyVendor\MyApp'))))->getInstance(ResourceInterface::class);
    // request
    $news = $resource
        ->get
        ->uri('app://self/news')
        ->withQuery(['date' => 'today'])
        ->request();
    // output
    echo $news;
}
//{
//    "headline": "40th anniversary of Rubik's Cube invention.",
//    "sports": "Pieter Weening wins Giro d'Italia.",
//    "_embedded": {
//    "weather": {
//        "today": "the weather of today is sunny",
//            "_links": {
//            "self": {
//                "href": "/weather?date=today"
//                },
//                "tomorrow": {
//                "href": "/weather/tomorrow"
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

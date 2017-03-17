<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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
    $start = microtime(true);
    // create resource client with HalModule
    $resource = (new Injector(new HalModule(new ResourceModule('MyVendor\MyApp'))))->getInstance(ResourceInterface::class);
    $cachedClient = serialize($resource);
    // request
    $news = $resource
        ->get
        ->uri('app://self/news')
        ->withQuery(['date' => 'today'])
        ->request();
    // output
    echo $news . PHP_EOL;
    $time1 = microtime(true) - $start;
    $start = microtime(true);
}
    $resource = unserialize($cachedClient);
    // request
    $news = $resource
        ->get
        ->uri('app://self/news')
        ->withQuery(['date' => 'today'])
        ->request();
    // output
    echo $news . PHP_EOL;
    $time2 = microtime(true) - $start;
    echo 'x' . round($time1 / $time2) . ' times faster.' . PHP_EOL;

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

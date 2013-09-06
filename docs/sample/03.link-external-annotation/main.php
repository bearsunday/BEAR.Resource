<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Resource\AbstractObject;
use Ray\Di\Injector;

main: {
    $resource = require __DIR__ . '/scripts/instance.php';
    $result = $resource->get->uri('app://self/entry')->linkCrawl('comment')->linkCrawl('nice')->eager->request();
    /* @var $result \BEAR\Resource|AbstractObject */
}

output: {
    print_r($result->body) . PHP_EOL;
}

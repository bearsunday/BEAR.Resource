<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

main: {
    $resource = require __DIR__ . '/scripts/instance.php';
    $result = $resource
        ->get
        ->uri('app://self/user')
        ->withQuery(['id' => 1])
        ->eager
        ->request();
    /* @var $result \BEAR\Resource|ResourceObject */
}

output: {
    print "code:{$result->code}" . PHP_EOL;

    print 'headers:' . PHP_EOL;
    print_r($result->headers) . PHP_EOL;

    print 'body:' . PHP_EOL;
    print_r($result->body) . PHP_EOL;
}

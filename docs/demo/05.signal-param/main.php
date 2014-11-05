<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

main: {
    $resource = require __DIR__ . '/scripts/instance.php';
    $user = $resource
        ->get
        ->uri('app://self/user')
        ->eager
        ->request();

    $delete = $resource
        ->delete
        ->uri('app://self/user')
        ->eager
        ->request();

    /* @var $result \BEAR\Resource|ResourceObject */
}

output: {
    echo "code:{$user->code}" . PHP_EOL;

    echo 'headers:' . PHP_EOL;
    print_r($user->headers) . PHP_EOL;

    echo 'body:' . PHP_EOL;
    print_r($user->body) . PHP_EOL;

    echo $delete->code;
}

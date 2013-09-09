<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

main: {
    $resource = require __DIR__ . '/scripts/instance.php';
    $user = $resource
        ->get
        ->uri('app://self/user')
        ->withQuery(['id' => 1])
        ->eager
        ->request();
}

output: {
    echo $user;
}

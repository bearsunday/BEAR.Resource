<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

main: {
    $resource = require __DIR__ . '/scripts/instance.php';
    $user = $resource
        ->get
        ->uri('app://self/user')
        ->withQuery(['id' => 0])
        ->linkNew('blog')
        ->eager
        ->request();
    /* @var $result \BEAR\Resource|ResourceObject */
}

output: {
    print_r($user->body) . PHP_EOL;
    print_r($user->body['blog']) . PHP_EOL;
}

//    Array
//    (
//        [name] => Athos
//        [age] => 15
//        [blog_id] => 0
//    )
//    Array
//    (
//        [name] => Athos blog
//    )

<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use BEAR\Resource\AbstractObject;
use Ray\Di\Injector;

main: {
    $resource = require __DIR__ . '/scripts/instance.php';
    list($user, $blog) = $resource
        ->get
        ->uri('app://self/user')
        ->withQuery(['id' => 0])
        ->linkNew('blog')
        ->eager
        ->request();
    /* @var $result \BEAR\Resource|AbstractObject */
}

output: {
    print_r($user) . PHP_EOL;
    print_r($blog) . PHP_EOL;
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

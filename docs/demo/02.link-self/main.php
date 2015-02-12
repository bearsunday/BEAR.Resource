<?php

use BEAR\Resource\Module\HalModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;
use BEAR\Resource\Module\ResourceModule;

main: {
    require __DIR__ . '/scripts/bootstrap.php';
    /* @var $resource \BEAR\Resource\ResourceInterface */
    $resource = (new Injector(new HalModule(new ResourceModule('Sandbox\Resource'))))->getInstance(ResourceInterface::class);
    $user = $resource
        ->get
        ->uri('app://self/user')
        ->withQuery(['id' => 2])
        ->linkNew('blog')
        ->eager
        ->request();
    /* @var $result \BEAR\Resource|ResourceObject */
}

output: {
    echo $user;
//    print_r($user->body) . PHP_EOL;
//    print_r($user->body['blog']) . PHP_EOL;
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

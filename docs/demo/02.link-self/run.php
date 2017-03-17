<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Injector;

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
}

output: {
    echo $user;
}

//{
//    "name": "Porthos",
//    "age": 17,
//    "blog_id": 2,
//    "blog": {
//    "name": "Porthos blog"
//    },
//    "_links": {
//    "self": {
//        "href": "/user?id=2"
//        },
//        "blog": {
//        "href": "app://self/blog?id=2"
//        }
//    }
//}

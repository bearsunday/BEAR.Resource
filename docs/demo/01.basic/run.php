<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

main: {
    require __DIR__ . '/scripts/bootstrap.php';
    /* @var $resource \BEAR\Resource\ResourceInterface */
    $resource = (new Injector(new ResourceModule('Sandbox\Resource')))->getInstance(ResourceInterface::class);

    /* @var $result ResourceObject */
    $result = $resource
        ->get
        ->uri('app://self/user')
        ->withQuery(['id' => 1])
        ->eager
        ->request();
}

output: {
    echo "code:{$result->code}" . PHP_EOL;

    echo 'headers:' . PHP_EOL;
    print_r($result->headers) . PHP_EOL;

    echo 'body:' . PHP_EOL;
    print_r($result->body) . PHP_EOL;
}
//code:200
//headers:
//Array
//(
//)
//body:
//Array
//(
//    [name] => Aramis
//    [age] => 16
//    [blog_id] => 1
//)

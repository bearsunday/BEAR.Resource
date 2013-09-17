<?php

$loader = require dirname(dirname(dirname(__DIR__))) . '/vendor/autoload.php';
$loader->add('', __DIR__);


$resource = require dirname(dirname(dirname(__DIR__))) . '/scripts/instance.php';
/* @var $resource \BEAR\Resource\Resource */

$result = $resource->get->object(new User)->withQuery(['id' => 1])->eager->request();
/* @var $result \BEAR\Resource|ResourceObject */

print "code:{$result->code}" . PHP_EOL;

print 'headers:' . PHP_EOL;
print_r($result->headers) . PHP_EOL;

print 'body:' . PHP_EOL;
print_r($result->body) . PHP_EOL;

//    code:200
//    headers:
//    Array
//    (
//    )
//    body:
//    Array
//    (
//        [name] => Aramis
//        [age] => 16
//        [blog_id] => 1
//    )
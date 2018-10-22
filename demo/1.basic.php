<?php

declare(strict_types=1);

namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

class User extends ResourceObject
{
    protected $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    public function onGet(string $id) : ResourceObject
    {
        $this->body = $this->users[$id];

        return $this;
    }
}

/* @var ResourceInterface $resource */
$resource = (new Injector(new ResourceModule('MyVendor\Demo'), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
/* @var $ro User */
$ro = $resource->uri('app://self/user')(['id' => 1]);
echo "code:{$ro->code}" . PHP_EOL;
echo 'headers:' . PHP_EOL;
print_r($ro->headers) . PHP_EOL;
echo 'body:' . PHP_EOL;
print_r($ro->body) . PHP_EOL;

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

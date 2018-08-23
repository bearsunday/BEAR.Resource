<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

class Blog extends ResourceObject
{
    protected $users = [
        ['name' => 'Athos blog'],
        ['name' => 'Aramis blog'],
        ['name' => 'Porthos blog']
    ];

    public function onGet(string $id) : ResourceObject
    {
        $this->body = $this->users[$id];

        return $this;
    }
}

class User extends ResourceObject
{
    private $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    /**
     * @Link(rel="blog", href="app://self/blog?id={blog_id}")
     */
    public function onGet($id) : ResourceObject
    {
        $this->body = $this->users[$id];

        return $this;
    }
}

/* @var ResourceInterface $resource */
$resource = (new Injector(new HalModule(new ResourceModule('MyVendor\Demo')), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
$user = $resource->get->uri('app://self/user')(['id' => 2]);

echo $user;
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

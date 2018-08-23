<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    public $uri = 'dummy://self/User';

    public $headers = [
        'x-header-test' => '123'
    ];

    private $users = [
        ['id' => 1, 'name' => 'Athos', 'age' => 15, 'blog_id' => 11],
        ['id' => 2, 'name' => 'Aramis', 'age' => 16, 'blog_id' => 12],
        ['id' => 3, 'name' => 'Porthos', 'age' => 17, 'blog_id' => 0]
    ];

    public function onGet(int $id)
    {
        if (! isset($this->users[$id])) {
            throw new \InvalidArgumentException((string) $id);
        }

        return $this->users[$id];
    }

    public function onPost($id, $name = 'default_name', $age = 99)
    {
        return "post user[{$id} {$name} {$age}]";
    }

    /**
     * @param string $noProvide
     *
     * @return string
     */
    public function onPut($noProvide)
    {
        return "$noProvide";
    }

    public function onPatch($id, $name = 'default_name', $age = 99)
    {
        return "patch user[{$id} {$name} {$age}]";
    }
}

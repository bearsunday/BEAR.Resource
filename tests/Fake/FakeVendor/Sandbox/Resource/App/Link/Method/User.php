<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Link\Method;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

/** @noinspection PhpUndefinedClassInspection */
class User extends ResourceObject
{
    private $users = [
        0 => ['name' => 'Athos', 'age' => 15, 'blog_id' => 11],
        1 => ['name' => 'Aramis', 'age' => 16, 'blog_id' => 12],
        2 => ['name' => 'Porthos', 'age' => 17, 'blog_id' => 0]
    ];

    /**
     * @Link(rel="prof", href="app://path/to/prof")
     * @Link(rel="blog", href="app://self/link/blog?id={id}")
     */
    public function onGet(int $id)
    {
        return $this->users[$id];
    }
}

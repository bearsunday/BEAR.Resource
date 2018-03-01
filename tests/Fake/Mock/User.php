<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Mock;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    public $links = [
        'friend' => [Link::HREF => 'app://self/friend/{?id}', Link::TEMPLATED => true],
        'profile' => [Link::HREF => 'app://self/profile']
    ];

    public $body = [
        'id' => 1,
        'name' => 'koriym'
    ];

    /**
     * @Link(rel="friend",  href="app://self/friend/{?id}")
     * @Link(rel="profile", href="app://self/profile{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }
}

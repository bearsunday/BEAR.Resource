<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Sandbox\Resource\Resource\App;

use BEAR\Resource\ResourceObject;

class Blog extends ResourceObject
{
    protected $users = [
        ['name' => 'Athos blog'],
        ['name' => 'Aramis blog'],
        ['name' => 'Porthos blog']
    ];

    public function onGet($id)
    {
        $this->body = $this->users[$id];

        return $this;
    }
}

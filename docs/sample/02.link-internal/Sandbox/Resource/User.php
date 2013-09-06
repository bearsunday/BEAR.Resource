<?php

namespace Sandbox\Resource;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class User extends AbstractObject
{

    protected $users = [
        ['name' => 'Athos', 'age' => 15, 'blog_id' => 0],
        ['name' => 'Aramis', 'age' => 16, 'blog_id' => 1],
        ['name' => 'Porthos', 'age' => 17, 'blog_id' => 2]
    ];

    public function onGet($id)
    {
        return $this->users;
    }

    public function onLinkView(AbstractObject $resource)
    {
        $html = <<<EOT
<div>{$resource->body['name']}<div>
EOT;

        return $html;
    }

}

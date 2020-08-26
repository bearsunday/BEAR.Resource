<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Marshal\Tag;

use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\Resource\App\Marshal\SelectTrait;

class Name extends ResourceObject
{
    use SelectTrait;

    private $repo = [
        [
            'id' => '1',
            'name' => 'zim',
        ],
        [
            'id' => '2',
            'name' => 'dib',
        ],
        [
            'id' => '3',
            'name' => 'gir',
        ],
    ];

    public function onGet($tag_id)
    {
        return $this->select('id', $tag_id);
    }
}

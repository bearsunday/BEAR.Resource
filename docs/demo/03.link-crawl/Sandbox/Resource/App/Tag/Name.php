<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Sandbox\Resource\App\Tag;

use BEAR\Resource\ResourceObject;
use Sandbox\Resource\App\SelectTrait;

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

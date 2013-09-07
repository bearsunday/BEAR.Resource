<?php
namespace Sandbox\Resource\App\Link;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Meta extends AbstractObject
{
    private $repo = [
        [
            [
                'id' => '1',
                'post_id' => '1',
                'data' => 'meta 1',
            ],
            [
                'id' => '2',
                'post_id' => '2',
                'data' => 'meta 2',
            ],
            [
                'id' => '3',
                'post_id' => '3',
                'data' => 'meta 3',
            ],
            [
                'id' => '4',
                'post_id' => '4',
                'data' => 'meta 4',
            ],
            [
                'id' => '5',
                'post_id' => '5',
                'data' => 'meta 5',
            ],
        ],
    ];

    /**
     * @param id
     *
     * @return array
     *
     */
    public function onGet()
    {
        var_dump(func_get_args());
    }
}

<?php
namespace TestVendor\Sandbox\Resource\App\Marshal;

use BEAR\Resource\ResourceObject;

class Meta extends ResourceObject
{
    use SelectTrait;

    private $repo = [
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
    ];

    /**
     * @param id
     *
     * @return array
     *
     */
    public function onGet($post_id)
    {
        return $this->select('id', $post_id);
    }
}

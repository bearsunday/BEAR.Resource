<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Marshal;

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
        [
            'id' => '6',
            'post_id' => '6',
            'data' => 'meta 6',
        ],
        [
            'id' => '7',
            'post_id' => '7',
            'data' => 'meta 7',
        ],
        [
            'id' => '8',
            'post_id' => '8',
            'data' => 'meta 8',
        ],
    ];

    /**
     * @return array
     */
    public function onGet($post_id)
    {
        return $this->select('id', $post_id);
    }
}

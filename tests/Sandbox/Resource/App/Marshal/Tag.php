<?php
namespace Sandbox\Resource\App\Marshal;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Tag extends AbstractObject
{
    use SelectTrait;

    private $repo = [
        [
            'id' => '1',
            'post_id' => '1',
            'tag_id'  => '1',
        ],
        [
            'id' => '2',
            'post_id' => '1',
            'tag_id'  => '2',
        ],
        [
            'id' => '3',
            'post_id' => '2',
            'tag_id'  => '2',
        ],
        [
            'id' => '4',
            'post_id' => '2',
            'tag_id'  => '3',
        ],
        [
            'id' => '5',
            'post_id' => '3',
            'tag_id'  => '3',
        ],
        [
            'id' => '6',
            'post_id' => '3',
            'tag_id'  => '1',
        ],
        [
            'id' => '7',
            'post_id' => '4',
            'tag_id'  => '1',
        ],
        [
            'id' => '8',
            'post_id' => '4',
            'tag_id'  => '2',
        ],
        [
            'id' => '9',
            'post_id' => '4',
            'tag_id'  => '3',
        ],
        [
            'id' => '10',
            'post_id' => '5',
            'tag_id'  => '2',
        ],
    ];

    /**
     * @param id
     *
     * @Link(crawl="tree", rel="tag_name",  href="app://self/marshal/tag/name?tag_id={tag_id}",  method="get")
     *
     */
    public function onGet($post_id)
    {
        return $this->select('post_id', $post_id);
    }
}

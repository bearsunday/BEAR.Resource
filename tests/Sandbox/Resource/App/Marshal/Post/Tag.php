<?php
namespace Sandbox\Resource\App\Link\Post;

use Sandbox\Resource\App\Link\SelectTrait;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Tag extends AbstractObject
{
    use SelectTrait;

    private $repo = [
        [
            'id' => 1,
            'post_id' => 1,
            'tag_id' => 1,
        ],
        [
            'id' => 2,
            'post_id' => 1,
            'tag_id' => 2,
        ],
        [
            'id' => 3,
            'post_id' => 2,
            'tag_id' => 2,
        ],
        [
            'id' => 4,
            'post_id' => 2,
            'tag_id' => 3,
        ],
        [
            'id' => 5,
            'post_id' => 3,
            'tag_id' => 3,
        ],
        [
            'id' => 6,
            'post_id' => 3,
            'tag_id' => 1,
        ],
        [
            'id' => 7,
            'post_id' => 4,
            'tag_id' => 1,
        ],
        [
            'id' => 8,
            'post_id' => 4,
            'tag_id' => 2,
        ],
        [
            'id' => 9,
            'post_id' => 4,
            'tag_id' => 3,
        ],
        [
            'id' => 10,
            'post_id' => 5,
            'tag_id' => 2,
        ],
    ];

    /**
     * @Link(crawl="tree", rel="meta", href="app://self/link/meta", method="get")
     */
    public function onGet($id)
    {
        $tag = $this->select('post_id', $id);
        return $tag;
    }
}

<?php
namespace Sandbox\Resource\App\Link;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Annotation\Link;

class Post extends AbstractObject
{
    use SelectTrait;

    private $repo = [
        [
            'id' => '1',
            'author_id' => '1',
            'body' => 'Anna post #1',
            'fake_field' => '69',
            'null_field' => null,
        ],
        [
            'id' => '2',
            'author_id' => '1',
            'body' => 'Anna post #2',
            'fake_field' => '69',
            'null_field' => null,
        ],
        [
            'id' => '3',
            'author_id' => '1',
            'body' => 'Anna post #3',
            'fake_field' => '69',
            'null_field' => null,
        ],
        [
            'id' => '4',
            'author_id' => '2',
            'body' => 'Clara post #1',
            'fake_field' => '88',
            'null_field' => null,
        ],
        [
            'id' => '5',
            'author_id' => '2',
            'body' => 'Clara post #2',
            'fake_field' => '88',
            'null_field' => null,
        ],
    ];

    /**
     * @Link(crawl="tree", rel="meta", href="app://self/link/meta", method="get")
     * @Link(crawl="tree", rel="tag", href="app://self/link/post/tag", method="get")
     */
    public function onGet($id)
    {
        $posts = $this->select('author_id', $id);
        return $posts;
    }
}

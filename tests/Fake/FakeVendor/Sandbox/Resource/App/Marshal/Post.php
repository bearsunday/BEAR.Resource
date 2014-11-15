<?php
namespace FakeVendor\Sandbox\Resource\App\Marshal;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Post extends ResourceObject
{
    private $repo = [
        'blog11' => [
            'id' => '1',
            'author_id' => '1',
            'body' => 'Anna post #1',
        ],
        'blog12' => [
            'id' => '2',
            'author_id' => '1',
            'body' => 'Anna post #2',
        ],
        'blog13' => [
            'id' => '3',
            'author_id' => '1',
            'body' => 'Anna post #3',
        ],
        'blog14' => [
            'id' => '4',
            'author_id' => '2',
            'body' => 'Clara post #1',
        ],
        'blog15' => [
            'id' => '5',
            'author_id' => '2',
            'body' => 'Clara post #2',
        ],
    ];

    /**
     * @Link(rel="meta", href="app://self/marshal/meta?post_id={id}", crawl="tree")
     * @Link(rel="tag",  href="app://self/marshal/tag?post_id={id}", crawl="tree")
     */
    public function onGet($blog_id)
    {
        $this->body = $this->repo['blog' . $blog_id];

        return $this;
    }
}

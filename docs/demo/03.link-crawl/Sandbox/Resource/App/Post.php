<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Sandbox\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Post extends ResourceObject
{
    use SelectTrait;

    private $repo = [
        [
            'id' => '1',
            'author_id' => '1',
            'body' => 'Anna post #1',
        ],
        [
            'id' => '2',
            'author_id' => '1',
            'body' => 'Anna post #2',
        ],
        [
            'id' => '3',
            'author_id' => '1',
            'body' => 'Anna post #3',
        ],
        [
            'id' => '4',
            'author_id' => '2',
            'body' => 'Clara post #1',
        ],
        [
            'id' => '5',
            'author_id' => '2',
            'body' => 'Clara post #2',
        ],
    ];

    /**
     * @Link(crawl="tree", rel="meta", href="app://self/meta?post_id={id}", method="get")
     * @Link(crawl="tree", rel="tag",  href="app://self/tag?post_id={id}",  method="get")
     */
    public function onGet($author_id)
    {
        $posts = $this->select('author_id', $author_id);

        return $posts;
    }
}

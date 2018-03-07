<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Marshal;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Tag extends ResourceObject
{
    use SelectTrait;

    private $repo = [
        [
            'id' => '1',
            'post_id' => '1',
            'tag_id' => '1',
        ],
        [
            'id' => '2',
            'post_id' => '1',
            'tag_id' => '2',
        ],
        [
            'id' => '3',
            'post_id' => '2',
            'tag_id' => '2',
        ],
        [
            'id' => '4',
            'post_id' => '2',
            'tag_id' => '3',
        ],
        [
            'id' => '5',
            'post_id' => '3',
            'tag_id' => '3',
        ],
        [
            'id' => '6',
            'post_id' => '3',
            'tag_id' => '1',
        ],
        [
            'id' => '7',
            'post_id' => '4',
            'tag_id' => '1',
        ],
        [
            'id' => '8',
            'post_id' => '4',
            'tag_id' => '2',
        ],
        [
            'id' => '9',
            'post_id' => '4',
            'tag_id' => '3',
        ],
        [
            'id' => '10',
            'post_id' => '5',
            'tag_id' => '2',
        ],
        [
            'id' => '11',
            'post_id' => '6',
            'tag_id' => '1',
        ],
        [
            'id' => '12',
            'post_id' => '7',
            'tag_id' => '2',
        ],
        [
            'id' => '13',
            'post_id' => '8',
            'tag_id' => '3',
        ],
    ];

    /**
     * @Link(crawl="tree", rel="tag_name",  href="app://self/marshal/tag/name?tag_id={tag_id}",  method="get")
     * @Link(crawl="tree", rel="tag_type", href="app://self/marshal/tag/type?tag_type=1")
     * @Link(crawl="another_tree", rel="xxx",  href="app://path/to/another/resource",  method="get")
     */
    public function onGet($post_id)
    {
        return $this->select('post_id', $post_id);
    }
}

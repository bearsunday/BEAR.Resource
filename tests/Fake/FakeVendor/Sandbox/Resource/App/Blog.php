<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Blog extends ResourceObject
{
    private array $repo = [
        11 => ['id' => 11, 'name' => 'Athos blog'],
        12 => ['id' => 12, 'name' => 'Aramis blog'],
        16 => ['id' => 16, 'name' => 'Porthos blog'],
        17 => [
            'id' => 17,
            'name' => 'My blog',
            'label' => [
                'a',
                'b'
            ],
            'keyword' => [
                'c',
                'd'
            ],
        ],
        99 => ['id' => 19, 'name' => 'BEAR blog'],
    ];

    /**
     * @Link(rel="post", href="app://self/marshal/post?blog_id={id}", crawl="tree")
     */
    #[Link(rel: "post", href: "app://self/marshal/post?blog_id={id}", crawl: "tree")]
    public function onGet(int $id)
    {
        $this->body = $this->repo[$id];

        return $this;
    }
}

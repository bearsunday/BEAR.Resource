<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Batch;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use FakeVendor\Sandbox\DataLoader\LikeDataLoader;

class LinkedComment extends ResourceObject
{
    #[Link(crawl: 'comment-tree', rel: 'like', href: 'app://self/batch/like?comment_id={id}#link=self:user', dataLoader: LikeDataLoader::class)]
    public function onGet(): static
    {
        $this->body = [['id' => 10]];

        return $this;
    }
}

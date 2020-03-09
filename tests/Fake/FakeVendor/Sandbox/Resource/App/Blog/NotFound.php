<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Blog;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class NotFound extends ResourceObject
{
    /**
     * @Link(href="app://self/user{?id}", rel="user", crawl="meta")
     */
    public function onGet()
    {
        $this->body = ['message' => 'blog not found'];
        $this->code = Code::NOT_FOUND;

        return $this;
    }
}

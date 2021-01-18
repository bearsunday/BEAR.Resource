<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Href;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Hasembed extends ResourceObject
{
    private $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @Embed(rel="bird1", src="app://self/bird/canary")
     * @Link(rel="next", href="app://self/href/target?id={id}")
     */
    #[Embed(rel: "bird1", src: "app://self/bird/canary")]
    #[Link(rel: "next", href: "app://self/href/target?id={id}")]
    public function onGet(int $id)
    {
        $this['next'] = $this->resource->href('next', ['id' => $id]);

        return $this;
    }
}

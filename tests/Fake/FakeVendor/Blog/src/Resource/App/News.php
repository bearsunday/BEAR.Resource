<?php

namespace FakeVendor\Blog\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    private $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @Embed(rel="weather",src="app://self/weather{?date}")
     */
    public function onGet($date)
    {
        $this['technology'] = "Microsoft to stop producing Windows versions";
        $this['user'] = $this->resource->get->uri('app://self/user')->withQuery(['id' => 2])->request();

        return $this;
    }
}

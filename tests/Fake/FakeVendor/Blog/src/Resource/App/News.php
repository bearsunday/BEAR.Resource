<?php

declare(strict_types=1);

namespace FakeVendor\Blog\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    public function __construct(private ResourceInterface $resource)
    {
    }

    /**
     * @Embed(rel="weather",src="app://self/weather{?date}")
     */
    #[Embed(rel: "weather",src: "app://self/weather{?date}")]
    public function onGet(string $date)
    {
        unset($date);
        $this['technology'] = 'Microsoft to stop producing Windows versions';
        $this['user'] = $this->resource->get->uri('app://self/user')->withQuery(['id' => 2])->request();

        return $this;
    }
}

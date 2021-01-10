<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    /** @var array{weather: \BEAR\Resource\Request} */
    public $body;

    #[Embed(rel: 'weather', src: 'app://self/weather{?date}')]
    #[Link(rel: 'event', href: 'app://self/event{?news_date}')]
    public function onGet(string $date): self
    {
        $this->body['news_date'] = $date;

        return $this;
    }
}

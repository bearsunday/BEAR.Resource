<?php

declare(strict_types=1);

namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    /**
     * @Embed(rel="weather", src="/weather{?date}")
     */
    public function onGet(string $date) : ResourceObject
    {
        $this->body += [
            'headline' => "40th anniversary of Rubik's Cube invention.",
            'sports' => "Pieter Weening wins Giro d'Italia."
        ];

        return $this;
    }
}

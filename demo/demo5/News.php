<?php

declare(strict_types=1);

namespace MyVendor\Demo\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    public function __construct(private readonly ResourceInterface $resource)
    {
    }

    #[Embed(rel: "weather", src: "app://self/weather{?date}")]
    public function onGet(string $date)
    {
        unset($date);
        $this['headline'] = "40th anniversary of Rubik's Cube invention.";
        $this['sports'] = "Pieter Weening wins Giro d'Italia.";

        return $this;
    }
}
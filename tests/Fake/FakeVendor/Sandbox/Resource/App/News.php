<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App;

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
        unset($date);
        $this['headline'] = "40th anniversary of Rubik's Cube invention.";
        $this['sports'] = "Pieter Weening wins Giro d'Italia.";
        $this['user'] = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->request();

        return $this;
    }
}

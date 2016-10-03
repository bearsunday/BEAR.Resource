<?php

namespace FakeVendor\Sandbox\Resource\App\User;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Entry extends ResourceObject
{
    public function __construct(ResourceInterface $resource = null)
    {
        $this->resource = $resource;
    }

    private $entries = [
        100 => ['id' => 100, 'title' => 'Entry1'],
        101 => ['id' => 101, 'title' => 'Entry2'],
        102 => ['id' => 102, 'title' => 'Entry3'],
    ];

    /**
     * @return array
     */
    public function onGet()
    {
        return $this->entries;
    }

    public function onLinkComment(ResourceObject $ro)
    {
        $request = $this->resource->get->uri('app://self/User/Entry/Comment')->withQuery(
            ['entry_id' => $ro->body['id']]
        )->request();

        return $request;
    }
}

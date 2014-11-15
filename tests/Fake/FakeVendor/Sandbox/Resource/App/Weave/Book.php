<?php

namespace FakeVendor\Sandbox\Resource\App\Weave;

use BEAR\Resource\Annotation\Log;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Book extends ResourceObject
{
    /**
     * @param \BEAR\Resource\ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource = null)
    {
    }

    /**
     * @param id
     *
     * @return array
     *
     * @Log
     */
    public function onGet($id)
    {
        return "book id[{$id}]";
    }
}

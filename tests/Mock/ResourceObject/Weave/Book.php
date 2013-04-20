<?php

namespace testworld\ResourceObject\Weave;

use BEAR\Resource\AbstractObject;

class Book extends AbstractObject
{

    /**
     * @param ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource = null)
    {
        if (is_null($resource)) {
            $resource = include dirname(dirname(dirname(__DIR__))) . '/scripts/resource.php';
        }
        $this->resource = $resource;
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

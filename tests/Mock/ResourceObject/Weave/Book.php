<?php

namespace testworld\ResourceObject\Weave;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource;

class Book extends AbstractObject
{

    /**
     * @param ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource = null)
    {
        if (is_null($resource)) {
            $resurce = include dirname(dirname(dirname(__DIR__))) . '/script/resource.php';
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

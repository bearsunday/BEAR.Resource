<?php

namespace testworld\ResourceObject;

use BEAR\Resource\Object as ResourceObject,
BEAR\Resource\AbstractObject;

/**
 * @Log
 * @Scope("singleton")
 */
class MethodAnnotation extends AbstractObject
{
    /**
     * @Log
     * @Get
     */
    public function read($id)
    {
        return "get {$id}";
    }

    /**
     * @Put
     */
    public function update($id, $title, $body)
    {
    }

    /**
     * @Post
     */
    public function create($title, $body)
    {
    }

    /**
     * @Delete
     */
    public function delete($id)
    {
    }

    /**
     * @Provides("id")
     */
    public function provideId()
    {
    }
}

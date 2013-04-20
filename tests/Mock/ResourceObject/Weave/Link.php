<?php

namespace testworld\ResourceObject\Weave;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;

class Link extends AbstractObject
{

    /**
     * @param animal
     *
     * @return string
     *
     * @Log
     */
    public function onGet($animal)
    {
        return "Like a {$animal} to a honey pot.";
    }

    /**
     * @param ResourceObject $resource
     *
     * @return string
     */
    public function onLinkView(ResourceObject $resource)
    {
        return "<html>$resource->body</html>";
    }
}

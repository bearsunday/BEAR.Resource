<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Object;

class Link extends AbstractObject
{
    /**
     * @param id
     *
     * @return string
     */
    public function onGet($id)
    {
        return "bear{$id}";
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function onLinkView(Object $resource)
    {
        return "<html>$resource->body</html>";
    }
}

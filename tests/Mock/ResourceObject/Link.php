<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\AbstractObject;

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
    public function onLinkView($string)
    {
        return "<html>$string</html>";
    }
}
<?php
namespace Sandbox\Resource\App\Link;

use BEAR\Resource\AbstractObject;

class Blog extends AbstractObject
{
    private $blogs = array(
        11 => array('id' => 11, 'name' => "Athos blog", 'inviter' => 2),
        12 => array('id' => 12, 'name' => "Aramis blog", 'inviter' => 2)
    );

    /**
     * @param id
     *
     * @return array
     */
    public function onGet($blog_id)
    {
        return $this->blogs[$blog_id];
    }
}

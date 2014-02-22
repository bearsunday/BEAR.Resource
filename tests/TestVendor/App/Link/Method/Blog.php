<?php
namespace TestVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\ResourceObject;

class Blog extends ResourceObject
{
    private $repo = array(
        11 => array('name' => "Athos blog"),
        12 => array('name' => "Aramis blog")
    );

    /**
     * @param id
     *
     * @return array
     */
    public function onGet($blog_id)
    {
        return $this->repo[$blog_id];
    }
}

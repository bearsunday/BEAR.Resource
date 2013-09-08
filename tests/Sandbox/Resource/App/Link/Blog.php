<?php
namespace Sandbox\Resource\App\Link;

use BEAR\Resource\AbstractObject;

class Blog extends AbstractObject
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
    public function onGet($id)
    {
        return $this->repo[$id];
    }
}

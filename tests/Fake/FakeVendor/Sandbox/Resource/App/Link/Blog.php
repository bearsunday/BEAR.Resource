<?php
namespace FakeVendor\Sandbox\Resource\App\Link;

use BEAR\Resource\ResourceObject;

class Blog extends ResourceObject
{
    private $repo = array(
        11 => ['name' => "Athos blog"],
        12 => ['name' => "Aramis blog"],
        99 => ['name' => "BEAR blog"],
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

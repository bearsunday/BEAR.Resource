<?php
namespace Sandbox\Resource\App\Link;

use BEAR\Resource\AbstractObject;

class Tag extends AbstractObject
{
    private $repo = [
        ['0' => 'hot'],
        ['title' => 'nice day']
    ];

    /**
     * @param id
     *
     * @return array
     */
    public function onGet()
    {
        return $this->repo;
    }
}

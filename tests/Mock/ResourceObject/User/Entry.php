<?php

namespace testworld\ResourceObject\User;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\Resource;

class Entry extends AbstractObject
{

    /**
     * @param ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource = null)
    {
        if (is_null($resource)) {
            $resurce = include dirname(dirname(dirname(__DIR__))) . '/scripts/resource.php';
        }
        $this->resource = $resource;
    }

    private $entries = array(
        100 => array('id' => 100, 'title' => "Entry1"),
        101 => array('id' => 101, 'title' => "Entry2"),
        102 => array('id' => 102, 'title' => "Entry3"),
    );

    /**
     * @param id
     *
     * @return array
     */
    public function onGet()
    {
//         $this['count'] =  count($this->entries);
//         $this['entry'] =  $this->entries;
        return $this->entries;
    }

    public function onLinkComment(ResourceObject $ro)
    {
        $request = $this->resource
        ->get->uri('app://self/User/Entry/Comment')->withQuery(['entry_id' => $ro->body['id']])->request();

        return $request;
    }
}

<?php

namespace testworld\ResourceObject\User\Entry;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\Factory;
use BEAR\Resource\Invoker;
use BEAR\Resource\Linker;
use BEAR\Resource\Resource;
use BEAR\Resource\Request;

class Comment extends AbstractObject
{

    /**
     * @param ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource = null)
    {
        if (is_null($resource)) {
            $resource = include dirname(dirname(dirname(dirname(__DIR__)))) . '/scripts/resource.php';
        }
        $this->resource = $resource;
    }

    private $comment = array(
        100 => array('id' => 100, 'title' => "Entry1"),
        101 => array('id' => 101, 'title' => "Entry2"),
        102 => array('id' => 102, 'title' => "Entry3"),
    );

    /**
     * @param id
     *
     * @return array
     */
    public function onGet($entry_id)
    {
        $comment = array('comment_id' => $entry_id + 100, 'body' => "entry $entry_id comment");

        return $comment;
    }

    public function onLinkThumbsUp(ResourceObject $ro)
    {
        $request = $this->resource->get->uri('app://self/Entry/Comment/ThumbsUp')->withQuery(
            ['comment_id' => $ro->body['comment_id']]
        )->request();

        return $request;
    }

    public function onLinkPoint($body)
    {
        return "Point of comment id {$body['comment_id']}";
    }
}

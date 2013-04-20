<?php

namespace testworld\ResourceObject;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\Invoker;
use Ray\Di\Injector;

class Shutdown extends \RuntimeException
{
}

;

class Blog extends AbstractObject
{

    /**
     * @param \BEAR\Resource\ResourceInterface $resource
     */
    public function __construct(ResourceInterface $resource = null)
    {
        if (is_null($resource)) {
            $resource = include dirname(dirname(__DIR__)) . '/scripts/resource.php';
        }
        $this->resource = $resource;
    }

    private $blogs = array(
        11 => array('id' => 11, 'name' => "Athos blog", 'inviter' => 2),
        12 => array('id' => 12, 'name' => "Aramis blog", 'inviter' => 2)
    );

    public function onPost()
    {
        throw new Shutdown('Service temporary shutdown.');
    }

    /**
     * @param id
     *
     * @return array
     */
    public function onGet($id)
    {
        return $this->blogs[$id];
    }

    public function onLinkInviter(ResourceObject $ro)
    {
        $request = $this->resource->get->uri('app://self/User')->withQuery(['id' => $ro->body['inviter']])->request();

        return $request;
    }
}

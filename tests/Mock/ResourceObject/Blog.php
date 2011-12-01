<?php

namespace testworld\ResourceObject;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource,
    BEAR\Resource\Factory,
    BEAR\Resource\Invoker,
    BEAR\Resource\Linker,
    BEAR\Resource\Client,
    BEAR\Resource\Request;

use Ray\Di\Annotation,
    Ray\Di\Config,
    Ray\Di\Forge,
    Ray\Di\Container,
    Ray\Di\Manager,
    Ray\Di\Injector,
    Ray\Di\EmptyModule;

class Blog extends AbstractObject
{

    /**
     * @param Resource $resource
     */
    public function __construct(Resource $resource = null)
    {
        if (is_null($resource)) {
            $resurce = include dirname(dirname(__DIR__)) . '/script/resource.php';
        }
        $this->resource = $resource;
    }

    private $blogs = array(
        11 => array('id' => 11, 'name' => "Athos blog", 'inviter' => 2),
        12 => array('id' => 12, 'name' => "Aramis blog", 'inviter' => 2)
    );

    public function onPost()
    {
        throw new \RuntimeException('Service temporary shutdown.');
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

    public function onLinkInviter(array $body)
    {
        $request = $this->resource
        ->get->uri('app://self/User')->withQuery(['id' => $body['inviter']])->request();
        return $request;
    }
}


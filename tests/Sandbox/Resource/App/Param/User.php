<?php

namespace Sandbox\Resource\App\Param;

use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Scope;

class User extends ResourceObject
{
    public function onGet($author_id)
    {
        return "author:{$author_id}";
    }

    public function onDelete($login_id)
    {
    }

    public function onPut($id)
    {
        $resource = $GLOBALS['RESOURCE'];
        /** @var $resource \BEAR\Resource\ResourceInterface */
        $resource->put->uri('app://self/param/user')->withQuery([])->eager->request();
    }

    /**
     * Provide '$delete_id' param in this method
     *
     * @return int
     */
    public function onProvidesAuthorId()
    {
        return 10;
    }
}

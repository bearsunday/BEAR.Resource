<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Param;

use BEAR\Resource\ResourceObject;

class User extends ResourceObject
{
    const PARAMETER_IN_SERVICE_EXCEPTION = 0;

    public function onGet($author_id)
    {
        if ($author_id === self::PARAMETER_IN_SERVICE_EXCEPTION) {
            // this cause exception
            $resource = $GLOBALS['RESOURCE'];
            /* @var $resource \BEAR\Resource\ResourceInterface */
            $resource->put->uri('app://self/param/user')->withQuery([])->eager->request();
        }

        return "author:{$author_id}";
    }

    public function onDelete($login_id)
    {
    }

    public function onPut($id)
    {
        unset($id);
        $resource = $GLOBALS['RESOURCE'];
        /* @var $resource \BEAR\Resource\ResourceInterface */
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

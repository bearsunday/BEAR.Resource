<?php

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakeAuthor extends ResourceObject
{
    /**
     * @Link(rel="friend", href="/friend?id={friend_id}")
     * @Link(rel="org", href="/org?id={org_id}")
     */
    public function onGet($id)
    {
        $this['id'] = $id;
        $this['friend_id'] = 'f' . $id;
        $this['org_id'] = 'o' . $id;

        return $this;
    }

    /**
     * @Link(rel="friend", href="/friend?id={friend_id}")
     */
    public function onPost($id)
    {
        $this['id'] = $id;
        $this['friend_id'] = 'f' . $id;

        return $this;
    }
}

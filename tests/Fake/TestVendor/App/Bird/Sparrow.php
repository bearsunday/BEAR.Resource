<?php

namespace TestVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;

class Sparrow extends ResourceObject
{
    public function onGet($id, $option = null)
    {
        $this['sparrow_id'] = $id;

        return $this;
    }

}

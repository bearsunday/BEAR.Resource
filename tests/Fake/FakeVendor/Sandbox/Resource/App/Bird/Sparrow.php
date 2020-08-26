<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\ResourceObject;

class Sparrow extends ResourceObject
{
    public function onGet($id, $option = null)
    {
        unset($option);
        $this['sparrow_id'] = $id;

        return $this;
    }
}

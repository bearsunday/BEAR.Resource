<?php

namespace BEAR\Resource;

class FakeParamResource
{
    public function onGet($id, $name = 'koriym')
    {
        return "$id $name";
    }
}

<?php

namespace BEAR\Resource\Mock;

use BEAR\Resource\Renderable;
use BEAR\Resource\Request;

class JsonRenderer implements Renderable
{
    public function render(Request $request, $data)
    {
        if ($data[0] === 'error') {
            throw new \RuntimeException;
        }
        return json_encode($data);
    }
}

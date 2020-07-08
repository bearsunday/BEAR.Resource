<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\Code;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public $headers = ['X-BEAR' => '1'];

    public function onGet($id = 0)
    {
        return $id;
    }

    public function onPost(string $name)
    {
        $this->code = Code::CREATED;
        return 'post ' . $name;
    }

    public function onPut(string $name)
    {
        return 'put ' . $name;
    }

    public function onPatch(string $name)
    {
        return 'patch ' . $name;
    }

    public function onDelete(string $name)
    {
        return 'delete ' . $name;
    }
}

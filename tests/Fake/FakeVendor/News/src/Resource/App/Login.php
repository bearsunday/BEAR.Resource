<?php

declare(strict_types=1);

namespace FakeVendor\News\Resource\App;

use BEAR\Resource\ResourceObject;

class Login extends ResourceObject
{
        public function onGet($name = ''): self
    {
        $this->body = [
            'nickname' => 'kumakun',
            'login_id' => 'login:' . $name
        ];

        return $this;
    }
}

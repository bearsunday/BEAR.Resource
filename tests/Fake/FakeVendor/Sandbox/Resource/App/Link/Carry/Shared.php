<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Link\Carry;

use BEAR\Resource\ResourceObject;

/** Answers every request with the one object the first request made */
class Shared extends ResourceObject
{
    public static self|null $instance = null;

    public function onGet(): self
    {
        if (self::$instance === null) {
            $this->headers = ['Surrogate-Key' => 'shared'];
            $this->body = ['name' => 'shared'];
            self::$instance = $this;
        }

        return self::$instance;
    }
}

<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Cache;

use BEAR\Resource\ResourceObject;

class Pdo extends ResourceObject
{
    public $time;

    public function __construct()
    {
        $this->time = microtime(true);
    }
}

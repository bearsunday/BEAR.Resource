<?php

namespace Sandbox\Resource\App\Cache;

use BEAR\Resource\ResourceObject;

class Pdo extends ResourceObject
{
    public $time;

    public function __construct()
    {
        $this->pdo = new \PDO('sqlite::memory:', null, null);
        $this->time = microtime(true);
    }
}

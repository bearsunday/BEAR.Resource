<?php

namespace Sandbox\Resource\App\Cache;

use BEAR\Resource\AbstractObject;

class Pdo extends AbstractObject
{
    public $time;

    public function __construct()
    {
        $this->pdo = new \PDO('sqlite::memory:', null, null);
        $this->time = microtime(true);
    }
}

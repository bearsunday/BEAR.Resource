<?php

namespace testworld\ResourceObject\Cache;

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
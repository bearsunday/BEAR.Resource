<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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

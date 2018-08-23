<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Factory;

use BEAR\Resource\ResourceObject as Ro;

class News extends Ro
{
    public function onGet($id)
    {
        return __CLASS__ . $id;
    }
}

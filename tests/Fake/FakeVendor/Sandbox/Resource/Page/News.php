<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\ResourceObject;

class News extends ResourceObject
{
    public function onGet(string $id)
    {
        return __CLASS__ . $id;
    }
}

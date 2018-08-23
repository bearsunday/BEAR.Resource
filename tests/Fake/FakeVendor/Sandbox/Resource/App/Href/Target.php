<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Href;

use BEAR\Resource\ResourceObject;

class Target extends ResourceObject
{
    public function onGet($id)
    {
        $this['id'] = $id;

        return $this;
    }
}

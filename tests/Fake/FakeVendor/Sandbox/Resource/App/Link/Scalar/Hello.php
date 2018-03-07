<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Link\Scalar;

use BEAR\Resource\ResourceObject;

class Hello extends ResourceObject
{
    public function onGet($name)
    {
        return "hell {$name}";
    }
}

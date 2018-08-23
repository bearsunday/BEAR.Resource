<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

class FakeChild extends ResourceObject
{
    public function onGet()
    {
        $this['tree'] = 3;

        return $this;
    }
}

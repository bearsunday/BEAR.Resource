<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

class FakeResourceParam implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        unset($uri);

        return new FakeNopResource;
    }
}

<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

class FakeResourceParam implements AdapterInterface
{
    public function get(AbstractUri $uri) : ResourceObject
    {
        unset($uri);

        return new FakeNopResource;
    }
}

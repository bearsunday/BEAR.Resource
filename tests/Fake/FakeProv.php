<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

class FakeProv implements AdapterInterface
{
    public function get(AbstractUri $uri)
    {
        unset($uri);

        return new NullResourceObject;
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;

class NotFoundBird extends ResourceObject
{
    /**
     * @Embed(rel="bird1", src="app://self/not_found_uri")
     */
    public function onGet($id)
    {
        return $this;
    }
}

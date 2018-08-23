<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Named;

class InvalidBird extends ResourceObject
{
    /**
     * @Named
     * @Embed(rel="bird1", src="invalid_uri")
     */
    public function onGet($id)
    {
        unset($id);

        return $this;
    }
}

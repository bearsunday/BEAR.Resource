<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Bird;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class BirdsRel extends ResourceObject
{
    /**
     * @Embed(rel="bird1", src="/bird/canary")
     * @Embed(rel="bird2", src="/bird/sparrow{?id}")
     * @Link(rel="bird3", href="/bird/suzume")
     */
    public function onGet(string $id)
    {
        unset($id);

        return $this;
    }
}

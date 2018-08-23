<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Weave;

use BEAR\Resource\ResourceObject;

class Link extends ResourceObject
{
    /**
     * @Log
     */
    public function onGet(string $animal)
    {
        return "Like a {$animal} to a honey pot.";
    }

    /**
     * @return string
     */
    public function onLinkView(ResourceObject $resource)
    {
        return "<html>$resource->body</html>";
    }
}

<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Holder extends ResourceObject
{
    public function __construct(ResourceInterface $resource)
    {
        $resource->get->uri('app://self/author?id=1')->eager->request();
    }

    public function onPost()
    {
        return true;
    }
}

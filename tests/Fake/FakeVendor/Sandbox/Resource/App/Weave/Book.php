<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Weave;

use BEAR\Resource\Annotation\FakeLog;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Book extends ResourceObject
{
    public function __construct(ResourceInterface $resource = null)
    {
    }

    /**
     * @FakeLog
     */
    public function onGet($id)
    {
        return "book id[{$id}]";
    }
}

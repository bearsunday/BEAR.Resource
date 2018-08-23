<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Href;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Origin extends ResourceObject
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @Link(rel="next", href="app://self/href/target?id={id}")
     */
    public function onGet(int $id)
    {
        $this['next'] = $this->resource->href('next', ['id' => $id]);

        return $this;
    }
}

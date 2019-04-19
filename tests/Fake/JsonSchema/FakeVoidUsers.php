<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\JsonSchema;

use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\ResourceObject;

class FakeVoidUsers extends ResourceObject
{
    /**
     * @JsonSchema(schema="users.json")
     */
    public function onGet(int $age)
    {
        return $this;
    }
}

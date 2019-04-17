<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Doc extends ResourceObject
{
    public $uri = 'app://self/Doc';

    /**
     * User
     *
     * Returns a variety of information about the user specified by the required $id parameter
     *
     * @param string $id User ID
     *
     * @Link(rel="friend", href="/fiend{?id}", method="get", title="Friend profile")
     * @Link(rel="task", href="/task{?id}")
     * @Embed(rel="profile", src="/profile{?id}")
     * @JsonSchema(schema="user.json")
     */
    public function onGet(string $id)
    {
        return $this;
    }

    /**
     * @param int    $id   ID
     * @param string $name Name
     * @param int    $age  Age
     */
    public function onPost($id, $name = 'default_name', $age = 99)
    {
        return $this;
    }

    public function onDelete()
    {
        return $this;
    }
}

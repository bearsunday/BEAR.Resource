<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App;

use BEAR\Resource\Annotation\Embed;
use BEAR\Resource\Annotation\JsonSchema;
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Doc extends ResourceObject
{
    /**
     * User
     *
     * Returns a variety of information about the user specified by the required $id parameter
     *
     * @param string $id User ID
     * @param int    $a
     */
    #[Link(rel: "friend", href: "/fiend{?id}", method: "get", title: "Friend profile")]
    #[Link(rel: "task", href: "/task{?id}")]
    #[Embed(rel: "profile", src: "/profile{?id}")]
    #[JsonSchema("user.json")]
    public function onGet(string $id, int $a)
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

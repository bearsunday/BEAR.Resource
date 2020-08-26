<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Annotation\Link;

class FakeHal extends ResourceObject
{
    /**
     * @Link(rel="profile", href="/profile")
     * @FakeNull
     */
    public function onGet(bool $change = false)
    {
        $fakeChild = (new FakeRo())(new FakeChild());
        $this->body = [
            'one' => 1,
            'two' => new Request((new InvokerFactory())(), $fakeChild)
        ];
        if ($change) {
            $this->body += [
                '_links' => ['profile' => ['href' => '/changed-profile']]
            ];
        }

        return $this;
    }
}

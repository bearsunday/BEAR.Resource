<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page;

use BEAR\Resource\FakeIntBacked;
use BEAR\Resource\FakeNotBacked;
use BEAR\Resource\FakeStringBacked;
use BEAR\Resource\ResourceObject;

final class EnumParam extends ResourceObject
{
    public function onGet(
        FakeStringBacked $stringBacked,
        FakeIntBacked $intBacked,
        FakeStringBacked|null $hasDefault = null,
    ): static {
        $this->body = [
            'stringBacked' => $stringBacked->value,
            'intBacked' => $intBacked->value
        ];

        return $this;
    }

    public function onPut(
        FakeNotBacked $notBacked,
    ): static {
        return $this;
    }
}

<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\Page\Layout;

use BEAR\Resource\FakeContentPage;
use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;

#[FakeContentPage]
class Content extends ResourceObject
{
    public function onGet(string $id, string $lang = 'ja'): static
    {
        $footer = $this->body['footer'] ?? null;
        $this->body['footerInOnGet'] = $footer instanceof Request ? $footer->toUri() : null;

        return $this;
    }
}

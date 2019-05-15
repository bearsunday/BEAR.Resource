<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class NullRenderer implements RenderInterface
{
    public function render(ResourceObject $ro)
    {
        unset($ro);

        return '';
    }
}

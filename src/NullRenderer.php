<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;

final class NullRenderer implements RenderInterface
{
    /** @psalm-pure */
    #[Override]
    public function render(ResourceObject $ro): string
    {
        unset($ro);

        return '';
    }
}

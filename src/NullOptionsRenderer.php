<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;
use Override;

final class NullOptionsRenderer implements RenderInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-pure
     */
    #[Override]
    public function render(ResourceObject $ro): never
    {
        throw new MethodNotAllowedException($ro::class . '::options', 405);
    }
}

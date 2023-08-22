<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;

final class NullOptionsRenderer implements RenderInterface
{
    /**
     * {@inheritDoc}
     */
    public function render(ResourceObject $ro)
    {
        throw new MethodNotAllowedException($ro::class . '::options', 405);
    }
}

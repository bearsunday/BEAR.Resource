<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodNotAllowedException;

use function get_class;

final class NullOptionsRenderer implements RenderInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(ResourceObject $ro)
    {
        throw new MethodNotAllowedException(get_class($ro) . '::options', 405);
    }
}

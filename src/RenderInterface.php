<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * Interface for render view
 */
interface RenderInterface
{
    /**
     * Render
     *
     * @return string
     */
    public function render(ResourceObject $ro);
}

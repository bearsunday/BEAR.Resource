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
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    public function render(ResourceObject $ro);
}

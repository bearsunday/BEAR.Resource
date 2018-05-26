<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
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

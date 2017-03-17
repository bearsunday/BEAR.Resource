<?php
/**
 * This file is part of the BEAR.Sunday package.
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
     * @param ResourceObject $resourceObject
     *
     * @return string
     */
    public function render(ResourceObject $resourceObject);
}

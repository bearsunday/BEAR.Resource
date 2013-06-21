<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
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
     * @param AbstractObject $resourceObject
     *
     * @return self
     */
    public function render(AbstractObject $resourceObject);
}

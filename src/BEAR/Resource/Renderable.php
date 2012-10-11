<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for render view
 *
 * @package BEAR.Resource
 */
interface Renderable
{
    /**
     * Render
     *
     * @param \BEAR\Resource\Object $resourceObject
     *
     * @return mixed
     */
    public function render(Object $resourceObject);
}

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
     * @param Request $request
     * @param array   $data
     *
     * @return string
     */
    public function render(Object $resourceObject);
}

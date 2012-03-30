<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for render view
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
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
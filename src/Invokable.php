<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Resource request invoke interface
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 *
 * @ImplementedBy("BEAR\Resource\Inboker")
 */
interface Invokable
{
    /**
     * Invokable resource request
     *
     * @param Request $request
     * @return mixed
     */
    public function invoke(Request $request);

    /**
     * Invokable traversal
     *
     * invoke callable
     *
     * @param \Traversable $requests
     */
    public function invokeTraversal(\Traversable $requests);

}

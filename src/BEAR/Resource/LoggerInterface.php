<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * Interface for resource logger
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @ImplementedBy("BEAR\Resource\Logger")
 *
 */
interface LoggerInterface
{
    /**
     * Log
     *
     * @param Request $request
     * @param mixed   $result
     *
     * @return void
     */
    public function log(Request $request, $result);
}

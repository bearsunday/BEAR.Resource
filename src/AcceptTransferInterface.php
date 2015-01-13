<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface AcceptTransferInterface
{
    /**
     * Accept resource object transfer service
     *
     * @param TransferInterface $responder Transfer service object
     * @param array             $server    $_SERVER
     */
    public function transfer(TransferInterface $responder, array $server);
}

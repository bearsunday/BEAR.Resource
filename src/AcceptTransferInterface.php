<?php

declare(strict_types=1);

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

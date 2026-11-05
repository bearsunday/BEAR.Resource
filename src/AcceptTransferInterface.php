<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-import-type Query from Types */
interface AcceptTransferInterface
{
    /**
     * Accept resource object transfer service
     *
     * @param TransferInterface $responder Transfer service object
     * @param Query             $server    $_SERVER
     *
     * @return void
     */
    public function transfer(TransferInterface $responder, array $server);
}

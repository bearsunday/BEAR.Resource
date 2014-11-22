<?php
/**
 * This file is part of the *** package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface AcceptTransferInterface
{
    public function transfer(TransferInterface $responder);
}

<?php
/**
 * This file is part of the *** package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface TransferInterface
{
    /**
     * Transfer resource object state
     */
    public function __invoke(ResourceObject $resourceObject, array $server);
}

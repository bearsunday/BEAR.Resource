<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

interface TransferInterface
{
    /**
     * Transfer resource object state
     *
     * @param ResourceObject $resourceObject Resource object
     * @param array          $server         $_SERVER value
     *
     * @return mixed
     */
    public function __invoke(ResourceObject $resourceObject, array $server);
}

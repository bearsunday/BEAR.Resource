<?php
/**
 * This file is part of the *** package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

interface TransferInterface
{
    /**
     * Transfer resource object state
     *
     * @param ResourceObject $resourceObject
     * @param array          $server
     */
    public function __invoke(ResourceObject $resourceObject, array $server);
}

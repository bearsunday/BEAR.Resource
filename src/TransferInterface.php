<?php

declare(strict_types=1);
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
     * @param ResourceObject $ro Resource object
     * @param array          $server         $_SERVER value
     *
     * @return mixed
     */
    public function __invoke(ResourceObject $ro, array $server);
}

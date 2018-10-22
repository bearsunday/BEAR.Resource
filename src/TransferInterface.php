<?php

declare(strict_types=1);

namespace BEAR\Resource;

interface TransferInterface
{
    /**
     * Transfer resource object state
     *
     * @param ResourceObject $ro     Resource object
     * @param array          $server $_SERVER value
     */
    public function __invoke(ResourceObject $ro, array $server);
}

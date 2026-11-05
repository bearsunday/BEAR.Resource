<?php

declare(strict_types=1);

namespace BEAR\Resource;

/** @psalm-import-type Query from Types */
interface TransferInterface
{
    /**
     * Transfer resource object state
     *
     * @param ResourceObject $ro     Resource object
     * @param Query          $server $_SERVER value
     *
     * @return void
     */
    public function __invoke(ResourceObject $ro, array $server);
}

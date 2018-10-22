<?php

declare(strict_types=1);

namespace BEAR\Resource;

final class NullResponder implements TransferInterface
{
    public function __invoke(ResourceObject $ro, array $server)
    {
    }
}

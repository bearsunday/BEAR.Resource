<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;

final class NullResponder implements TransferInterface
{
    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function __invoke(ResourceObject $ro, array $server)
    {
    }
}

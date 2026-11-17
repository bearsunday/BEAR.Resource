<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;
use Ray\Di\InjectorInterface;

final class AssistedParam implements ParamInterface
{
    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     * @psalm-capabilities read-props
     */
    #[Override]
    public function __invoke(string $varName, array $query, InjectorInterface $injector): void
    {
        unset($varName, $query, $injector);
    }
}

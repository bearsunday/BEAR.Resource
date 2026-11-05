<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\HalRenderer;
use BEAR\Resource\RenderInterface;
use Override;
use Ray\Di\AbstractModule;

/**
 * Provides RenderInterface interceptor bindings
 */
final class HalModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-this-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(RenderInterface::class)->to(HalRenderer::class);
    }
}

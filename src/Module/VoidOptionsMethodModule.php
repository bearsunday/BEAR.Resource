<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\NullOptionsRenderer;
use BEAR\Resource\RenderInterface;
use Override;
use Ray\Di\AbstractModule;

/**
 * Provides RenderInterface-options bindings
 */
final class VoidOptionsMethodModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(NullOptionsRenderer::class);
    }
}

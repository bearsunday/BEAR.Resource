<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\OptionsRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;

/**
 * Provides RenderInterface-options bindings
 */
final class OptionsMethodModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(OptionsRenderer::class);
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\OptionsBody;
use BEAR\Resource\OptionsMethods;
use BEAR\Resource\OptionsRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;

/**
 * Provides OptionsMethods and derived bindings
 *
 * The following module is installed:
 *
 * -OptionsBody
 * OptionsMethods
 * RenderInterface-options
 */
final class OptionsMethodHeaderModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind(OptionsMethods::class);
        $this->bind()->annotatedWith(OptionsBody::class)->toInstance(false);
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(OptionsRenderer::class);
    }
}

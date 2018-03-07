<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\OptionsRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;

class OptionsMethodModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(OptionsRenderer::class);
    }
}

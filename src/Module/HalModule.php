<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\HalRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;

class HalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RenderInterface::class)->to(HalRenderer::class);
    }
}

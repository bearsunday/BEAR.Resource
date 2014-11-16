<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\HalRenderer;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\UriMapper;
use BEAR\Resource\UriMapperInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class HalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(UriMapperInterface::class)->to(UriMapper::class)->in(Scope::SINGLETON);
        $this->bind(RenderInterface::class)->to(HalRenderer::class)->in(Scope::SINGLETON);
    }
}

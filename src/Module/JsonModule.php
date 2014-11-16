<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\JsonRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class JsonModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RenderInterface::class)->to(JsonRenderer::class)->in(Scope::SINGLETON);
    }
}

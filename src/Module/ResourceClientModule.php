<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Anchor;
use BEAR\Resource\AnchorInterface;
use BEAR\Resource\Factory;
use BEAR\Resource\FactoryInterface;
use BEAR\Resource\Invoker;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\JsonRenderer;
use BEAR\Resource\Linker;
use BEAR\Resource\LinkerInterface;
use BEAR\Resource\NamedParameter;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Resource\ParamHandlerInterface;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\Resource;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceParamHandler;
use BEAR\Resource\SchemeCollectionInterface;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\Cache;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceClientModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     *
     * @throws \Ray\Di\Exception\NotFound
     */
    protected function configure()
    {
        $this->bind(ResourceInterface::class)->to(Resource::class)->in(Scope::SINGLETON);
        $this->bind(InvokerInterface::class)->to(Invoker::class)->in(Scope::SINGLETON);
        $this->bind(LinkerInterface::class)->to(Linker::class)->in(Scope::SINGLETON);
        $this->bind(FactoryInterface::class)->to(Factory::class)->in(Scope::SINGLETON);
        $this->bind(SchemeCollectionInterface::class)->toProvider(SchemeCollectionProvider::class)->in(Scope::SINGLETON);
        $this->bind(AnchorInterface::class)->to(Anchor::class)->in(Scope::SINGLETON);
        $this->bind(NamedParameterInterface::class)->to(NamedParameter::class)->in(Scope::SINGLETON);
        $this->bind(RenderInterface::class)->to(JsonRenderer::class)->in(Scope::SINGLETON);
        $this->bind(Cache::class)->to(ArrayCache::class);
        $this->bind(ParamHandlerInterface::class)->to(ResourceParamHandler::class);
    }
}

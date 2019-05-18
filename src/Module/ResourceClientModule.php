<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Anchor;
use BEAR\Resource\AnchorInterface;
use BEAR\Resource\ExtraMethodInvoker;
use BEAR\Resource\Factory;
use BEAR\Resource\FactoryInterface;
use BEAR\Resource\HalLink;
use BEAR\Resource\Invoker;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\Linker;
use BEAR\Resource\LinkerInterface;
use BEAR\Resource\LoggerInterface;
use BEAR\Resource\NamedParameter;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Resource\NamedParamMetas;
use BEAR\Resource\NamedParamMetasInterface;
use BEAR\Resource\NullLogger;
use BEAR\Resource\NullReverseLink;
use BEAR\Resource\OptionsMethods;
use BEAR\Resource\OptionsRenderer;
use BEAR\Resource\PrettyJsonRenderer;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\Resource;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ReverseLinkInterface;
use BEAR\Resource\SchemeCollectionInterface;
use BEAR\Resource\UriFactory;
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
        $this->bind(UriFactory::class);
        $this->bind(ResourceInterface::class)->to(Resource::class)->in(Scope::SINGLETON);
        $this->bind(InvokerInterface::class)->to(Invoker::class);
        $this->bind(LinkerInterface::class)->to(Linker::class);
        $this->bind(FactoryInterface::class)->to(Factory::class);
        $this->bind(SchemeCollectionInterface::class)->toProvider(SchemeCollectionProvider::class);
        $this->bind(AnchorInterface::class)->to(Anchor::class);
        $this->bind(NamedParameterInterface::class)->to(NamedParameter::class);
        $this->bind(RenderInterface::class)->to(PrettyJsonRenderer::class)->in(Scope::SINGLETON);
        $this->bind(Cache::class)->to(ArrayCache::class);
        $this->bind(RenderInterface::class)->annotatedWith('options')->to(OptionsRenderer::class);
        $this->bind(OptionsMethods::class);
        $this->bind(NamedParamMetasInterface::class)->to(NamedParamMetas::class);
        $this->bind(ExtraMethodInvoker::class);
        $this->bind(HalLink::class);
        $this->bind(ReverseLinkInterface::class)->to(NullReverseLink::class);
        $this->bind(LoggerInterface::class)->to(NullLogger::class);
    }
}

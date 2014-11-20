<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\A;
use BEAR\Resource\Anchor;
use BEAR\Resource\AnchorInterface;
use BEAR\Resource\Exception\InvalidAppNameException;
use BEAR\Resource\Factory;
use BEAR\Resource\FactoryInterface;
use BEAR\Resource\HrefInterface;
use BEAR\Resource\Invoker;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\JsonRenderer;
use BEAR\Resource\Linker;
use BEAR\Resource\LinkerInterface;
use BEAR\Resource\NamedParameter;
use BEAR\Resource\NamedParameterInterface;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\Resource;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SchemeCollectionInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class ResourceClientModule extends AbstractModule
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @param string $appName
     */
    public function __construct($appName)
    {
        if (! is_string($appName)) {
            throw new InvalidAppNameException(gettype($appName));
        }
        $this->appName = $appName;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith('app_name')->toInstance($this->appName);
        $this->bind(ResourceInterface::class)->to(Resource::class)->in(Scope::SINGLETON);
        $this->bind(InvokerInterface::class)->to(Invoker::class)->in(Scope::SINGLETON);
        $this->bind(LinkerInterface::class)->to(Linker::class)->in(Scope::SINGLETON);
        $this->bind(FactoryInterface::class)->to(Factory::class)->in(Scope::SINGLETON);
        $this->bind(SchemeCollectionInterface::class)->toProvider(SchemeCollectionProvider::class)->in(Scope::SINGLETON);
        $this->bind(AnchorInterface::class)->to(Anchor::class)->in(Scope::SINGLETON);
        $this->bind(NamedParameterInterface::class)->to(NamedParameter::class)->in(Scope::SINGLETON);
        $this->bind(RenderInterface::class)->to(JsonRenderer::class)->in(Scope::SINGLETON);
    }
}

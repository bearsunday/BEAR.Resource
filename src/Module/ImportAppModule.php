<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\ContextualModule;
use BEAR\Resource\ImportApp;
use BEAR\Resource\SchemeCollectionInterface;
use Ray\Compiler\DiCompiler;
use Ray\Di\AbstractModule;

class ImportAppModule extends AbstractModule
{
    /**
     * Import scheme config
     *
     * @var array [$host,,][]
     */
    private $importAppConfig = [];

    /**
     * Default context namespace
     *
     * @var string
     */
    private $defaultContextName;

    /**
     * @var ContextualModule
     */
    private $contextualModule;

    /**
     * @param ImportApp[] $importApps
     */
    public function __construct(array $importApps, $defaultContextName = '')
    {
        $this->contextualModule = new ContextualModule($defaultContextName);
        foreach ($importApps as $importApp) {
            // create import config
            $this->importAppConfig[] = $importApp;
        }
        $this->defaultContextName = $defaultContextName;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith(ImportAppConfig::class)->toInstance($this->importAppConfig);
        $this->bind(SchemeCollectionInterface::class)->toProvider(ImportSchemeCollectionProvider::class);
    }
}

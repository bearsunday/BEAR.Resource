<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\SchemeCollectionInterface;
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
     * @param array  $importApps
     * @param string $defaultContextName
     */
    public function __construct(array $importApps, $defaultContextName = '')
    {
        foreach ($importApps as $importApp) {
            // create import config
            $this->importAppConfig[] = $importApp;
        }
        $this->defaultContextName = $defaultContextName;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Ray\Di\Exception\NotFound
     */
    protected function configure()
    {
        $this->bind()->annotatedWith(ImportAppConfig::class)->toInstance($this->importAppConfig);
        $this->bind(SchemeCollectionInterface::class)->toProvider(ImportSchemeCollectionProvider::class);
    }
}

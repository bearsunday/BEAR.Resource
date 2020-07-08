<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\ImportApp;
use BEAR\Resource\SchemeCollectionInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Exception\NotFound;

class ImportAppModule extends AbstractModule
{
    /**
     * Import scheme config
     *
     * @var array<ImportApp>
     */
    private $importAppConfig = [];

    /**
     * Default context namespace
     *
     * @var string
     */
    private $defaultContextName;

    /**
     * @param array<ImportApp> $importApps
     */
    public function __construct(array $importApps, string $defaultContextName = '')
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
     * @throws NotFound
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith(ImportAppConfig::class)->toInstance($this->importAppConfig);
        $this->bind(SchemeCollectionInterface::class)->toProvider(ImportSchemeCollectionProvider::class);
    }
}

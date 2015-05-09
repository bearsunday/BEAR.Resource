<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\ImportSchemeConfig;
use BEAR\Resource\ContextualModule;
use BEAR\Resource\SchemeCollectionInterface;
use Ray\Compiler\DiCompiler;
use Ray\Di\AbstractModule;

class ImportAppModule extends AbstractModule
{
    /**
     * Import scheme config
     *
     * @var array [$host, $scriptDir, $name][]
     */
    private $schemeConfig = [];

    private $defaultContextName;

    /**
     * @var ContextualModule
     */
    private $contextualModule;

    /**
     * @param array $configs [[host => [application name, context]]
     */
    public function __construct(array $configs, $defaultContextName = '')
    {
        $this->contextualModule = new ContextualModule($defaultContextName);
        foreach ($configs as $host => $config) {
            // create import config
            list($appName, $context, $scriptDir) = $this->getSchemeConfig($config, $host);
            $this->compile($scriptDir, $context, $appName);
            $this->schemeConfig[] = [$host, $scriptDir, $appName];
        }
        $this->defaultContextName = $defaultContextName;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith(ImportSchemeConfig::class)->toInstance($this->schemeConfig);
        $this->bind(SchemeCollectionInterface::class)->toProvider(ImportSchemeCollectionProvider::class);
    }

    /**
     * @param array  $config
     * @param string $host
     *
     * @return array [$name, $context, $scriptDir]
     */
    private function getSchemeConfig(array $config, $host)
    {
        list($name, $context) = $config;
        $appModule = $name . '\Module\AppModule';
        $tmpDir = dirname(dirname(dirname((new \ReflectionClass($appModule))->getFileName()))) . '/var/tmp';
        $scriptDir = sprintf('%s/%s', $tmpDir, $context);
        $this->schemeConfig[] = [$host, $scriptDir, $name];

        return [$name, $context, $scriptDir];
    }

    /**
     * @param string $scriptDir
     * @param string $context
     * @param string $appName
     *
     */
    private function compile($scriptDir, $context, $appName)
    {
        $module = $this->contextualModule->__invoke($context, $appName);
        if (! file_exists($scriptDir)) {
            mkdir($scriptDir);
        }
        $compiler = new DiCompiler($module, $scriptDir);
        $compiler->compile();
    }
}

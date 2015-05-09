<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\ImportSchemeConfig;
use BEAR\Resource\Exception\SchemeException;
use BEAR\Resource\SchemeCollectionInterface;
use Ray\Compiler\DiCompiler;
use Ray\Di\AbstractModule;

class ImportAppModule extends AbstractModule
{
    private $schemeConfig = [];

    /**
     * @param array $configs [[host => [application name, context]]
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $host => $config) {
            list($name, $context) = $config;
            $appModule = $name . '\Module\AppModule';
            $tmpDir = dirname(dirname(dirname((new \ReflectionClass($appModule))->getFileName()))) . '/var/tmp';
            $scriptDir = sprintf('%s/%s', $tmpDir, $context);
            if (! file_exists($scriptDir)) {
                mkdir($scriptDir);
            }
            $this->schemeConfig[] = [$host, $scriptDir, $name];
            if ($host === 'self') {
                continue;
            }
            $config[] = $scriptDir;
            $module = $this->getContextualModule($context, $name);
            if (! file_exists($scriptDir)) {
                mkdir($scriptDir);
            }
            $compiler = new DiCompiler($module, $scriptDir);
            $compiler->compile();
            $this->schemeConfig[] = [$host, $scriptDir, $name];
        }
        parent::__construct();
    }

    private function getContextualModule($contexts, $name)
    {
        $contextsArray = array_reverse(explode('-', $contexts));
        $module = null;
        foreach ($contextsArray as $context) {
            $class = $name . '\Module\\' . ucwords($context) . 'Module';
            if (!class_exists($class)) {
                $class = 'BEAR\Package\Context\\' . ucwords($context) . 'Module';
            }
            if (! is_a($class, AbstractModule::class, true)) {
                throw new SchemeException($class);
            }
            /* @var $module AbstractModule */
            $module = new $class($module);
        }

        return $module;
    }
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind()->annotatedWith(ImportSchemeConfig::class)->toInstance($this->schemeConfig);
        $this->bind(SchemeCollectionInterface::class)->toProvider(ImportSchemeCollectionProvider::class);
    }
}

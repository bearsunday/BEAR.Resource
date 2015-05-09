<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\Annotation\ImportSchemeConfig;
use BEAR\Resource\AppAdapter;
use Ray\Compiler\ScriptInjector;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

class ImportSchemeCollectionProvider implements ProviderInterface
{
    private $schemeConfig;

    /**
     * @var string
     */
    private $appName;

    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @param string            $appName
     * @param array             $schemeConfig
     * @param InjectorInterface $injector
     *
     * @AppName("appName")
     * @ImportSchemeConfig("schemeConfig")
     */
    public function __construct($appName, array $schemeConfig, InjectorInterface $injector)
    {
        $this->appName = $appName;
        $this->schemeConfig = $schemeConfig;
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     *
     * @return \BEAR\Resource\SchemeCollection
     */
    public function get()
    {
        $schemeCollection = (new SchemeCollectionProvider($this->appName, $this->injector))->get();
        foreach ($this->schemeConfig as $config) {
            list($host, $scriptDir, $name) = $config;
            $adapter = new AppAdapter(new ScriptInjector($scriptDir), $name);
            $schemeCollection
                ->scheme('page')->host($host)->toAdapter($adapter)
                ->scheme('app')->host($host)->toAdapter($adapter);
        }

        return $schemeCollection;
    }
}

<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\ImportApp;
use Ray\Compiler\ScriptInjector;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

class ImportSchemeCollectionProvider implements ProviderInterface
{
    /**
     * @var ImportApp[]
     */
    private $importAppConfig;

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
     * @param array             $importAppConfig
     * @param InjectorInterface $injector
     *
     * @AppName("appName")
     * @ImportAppConfig("importAppConfig")
     */
    public function __construct($appName, array $importAppConfig, InjectorInterface $injector)
    {
        $this->appName = $appName;
        $this->importAppConfig = $importAppConfig;
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
        foreach ($this->importAppConfig as $importApp) {
            $adapter = new AppAdapter(new ScriptInjector($importApp->scriptDir), $importApp->appName);
            $schemeCollection
                ->scheme('page')->host($importApp->host)->toAdapter($adapter)
                ->scheme('app')->host($importApp->host)->toAdapter($adapter);
        }

        return $schemeCollection;
    }
}

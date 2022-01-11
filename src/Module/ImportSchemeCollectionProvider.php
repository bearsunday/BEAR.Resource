<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\ImportApp;
use BEAR\Resource\SchemeCollection;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

final class ImportSchemeCollectionProvider implements ProviderInterface
{
    /** @var ImportApp[] */
    private array $importAppConfig;
    private string $appName;
    private InjectorInterface $injector;

    /**
     * @param ImportApp[] $importAppConfig
     *
     * @AppName("appName")
     * @ImportAppConfig("importAppConfig")
     */
    #[AppName('appName'), ImportAppConfig('importAppConfig')]
    public function __construct(string $appName, array $importAppConfig, InjectorInterface $injector)
    {
        $this->appName = $appName;
        $this->importAppConfig = $importAppConfig;
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     *
     * @return SchemeCollection
     */
    public function get()
    {
        $schemeCollection = (new SchemeCollectionProvider($this->appName, $this->injector))->get();
        foreach ($this->importAppConfig as $importApp) {
            $adapter = new AppAdapter($this->injector, $importApp->appName);
            $schemeCollection
                ->scheme('page')->host($importApp->host)->toAdapter($adapter)
                ->scheme('app')->host($importApp->host)->toAdapter($adapter);
        }

        return $schemeCollection;
    }
}

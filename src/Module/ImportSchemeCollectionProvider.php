<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\Annotation\ImportAppConfig;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\ImportApp;
use BEAR\Resource\SchemeCollection;
use Override;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollection> */
final readonly class ImportSchemeCollectionProvider implements ProviderInterface
{
    /**
     * @param ImportApp[] $importAppConfig
     *
     * @psalm-pure
     */
    public function __construct(
        #[AppName]
        private string $appName,
        #[ImportAppConfig]
        private array $importAppConfig,
        private InjectorInterface $injector,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-props
     */
    #[Override]
    public function get(): SchemeCollection
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

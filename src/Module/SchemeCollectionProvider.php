<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\HttpAdapter;
use BEAR\Resource\SchemeCollection;
use Override;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollection> */
final readonly class SchemeCollectionProvider implements ProviderInterface
{
    /** @psalm-pure */
    public function __construct(
        #[AppName]
        private string $appName,
        private InjectorInterface $injector,
    ) {
    }

    /**
     * Return instance
     *
     * @psalm-capabilities read-props|write-props
     */
    #[Override]
    public function get(): SchemeCollection
    {
        $schemeCollection = new SchemeCollection();
        $pageAdapter = new AppAdapter($this->injector, $this->appName);
        $appAdapter = new AppAdapter($this->injector, $this->appName);
        $schemeCollection->scheme('page')->host('self')->toAdapter($pageAdapter);
        $schemeCollection->scheme('app')->host('self')->toAdapter($appAdapter);
        $schemeCollection->scheme('http')->host('self')->toAdapter(new HttpAdapter($this->injector));

        return $schemeCollection;
    }
}

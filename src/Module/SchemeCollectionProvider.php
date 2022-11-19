<?php

declare(strict_types=1);

namespace BEAR\Resource\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Resource\AppAdapter;
use BEAR\Resource\HttpAdapter;
use BEAR\Resource\SchemeCollection;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

/** @implements ProviderInterface<SchemeCollection> */
final class SchemeCollectionProvider implements ProviderInterface
{
    /** @AppName("appName") */
    #[AppName('appName')]
    public function __construct(private string $appName, private InjectorInterface $injector)
    {
    }

    /**
     * Return instance
     */
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

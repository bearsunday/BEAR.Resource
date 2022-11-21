<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;

class FakeSchemeCollectionProvider implements ProviderInterface
{
    public function __construct(private InjectorInterface $injector)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get(): SchemeCollection
    {
        return (new SchemeCollection())
            ->scheme('app')->host('self')->toAdapter(new AppAdapter($this->injector, 'FakeVendor\Sandbox'))
            ->scheme('page')->host('self')->toAdapter(new AppAdapter($this->injector, 'FakeVendor\Sandbox'))
            ->scheme('nop')->host('self')->toAdapter(new FakeNop());
    }
}

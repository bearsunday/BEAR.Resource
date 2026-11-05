<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;
use Ray\Di\InjectorInterface;

final readonly class HttpAdapter implements AdapterInterface
{
    /** @psalm-pure */
    public function __construct(
        private InjectorInterface $injector,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-capabilities read-props|write-props|read-globals|write-globals|io|time
     */
    #[Override]
    public function get(AbstractUri $uri): ResourceObject
    {
        return $this->injector->getInstance(HttpResourceObject::class);
    }
}

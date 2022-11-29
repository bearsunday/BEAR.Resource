<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class HttpAdapter implements AdapterInterface
{
    public function __construct(
        private InjectorInterface $injector,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function get(AbstractUri $uri): ResourceObject
    {
        return $this->injector->getInstance(HttpResourceObject::class);
    }
}

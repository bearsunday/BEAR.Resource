<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

final class HttpAdapter implements AdapterInterface
{
    private InjectorInterface $injector;

    /**
     * @param InjectorInterface $injector Application dependency injector
     */
    public function __construct(InjectorInterface $injector)
    {
        $this->injector = $injector;
    }

    /**
     * {@inheritdoc}
     */
    public function get(AbstractUri $uri): ResourceObject
    {
        return $this->injector->getInstance(HttpResourceObject::class);
    }
}

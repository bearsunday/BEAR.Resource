<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Ray\Di\InjectorInterface;

use function assert;

final class HttpAdapter implements AdapterInterface
{
    /** @var InjectorInterface */
    private $injector;

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
        unset($uri);

        $httpRo = $this->injector->getInstance(HttpResourceObject::class);
        assert($httpRo instanceof HttpResourceObject);

        return $httpRo;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Throwable;

use function assert;
use function sprintf;
use function str_replace;
use function substr;
use function ucwords;

final class AppAdapter implements AdapterInterface
{
    /**
     * Resource adapter namespace
     */
    private string $namespace;

    /**
     * @param InjectorInterface $injector  Application dependency injector
     * @param string            $namespace Resource adapter namespace
     */
    public function __construct(private InjectorInterface $injector, string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ResourceNotFoundException
     * @throws Unbound
     */
    public function get(AbstractUri $uri): ResourceObject
    {
        if (substr($uri->path, -1) === '/') {
            $uri->path .= 'index';
        }

        $path = str_replace('-', '', ucwords($uri->path, '/-'));
        /** @var ''|class-string $class */
        $class = sprintf('%s\Resource\%s', $this->namespace, str_replace('/', '\\', ucwords($uri->scheme) . $path));
        try {
            $instance = $this->injector->getInstance($class);
            assert($instance instanceof ResourceObject);
        } catch (Unbound $e) {
            throw $this->getNotFound($uri, $e, $class);
        }

        return $instance;
    }

    /** @return ResourceNotFoundException|Unbound */
    private function getNotFound(AbstractUri $uri, Unbound $e, string $class): Throwable
    {
        $unboundClass = $e->getMessage();
        if ($unboundClass === "{$class}-") {
            return new ResourceNotFoundException((string) $uri, 404, $e);
        }

        return $e;
    }
}

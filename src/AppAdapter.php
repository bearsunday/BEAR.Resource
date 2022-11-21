<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use Ray\Di\Exception\Unbound;
use Ray\Di\InjectorInterface;
use Throwable;

use function assert;
use function sprintf;
use function str_ends_with;
use function str_replace;
use function ucwords;

final class AppAdapter implements AdapterInterface
{
    /**
     * @param InjectorInterface $injector  Application dependency injector
     * @param string            $namespace Resource adapter namespace
     */
    public function __construct(
        private InjectorInterface $injector,
        /** Resource adapter namespace */
        private string $namespace,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws ResourceNotFoundException
     * @throws Unbound
     */
    public function get(AbstractUri $uri): ResourceObject
    {
        if (str_ends_with($uri->path, '/')) {
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

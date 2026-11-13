<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;
use Ray\Di\Di\Set;
use Ray\Di\ProviderInterface;

use function assert;
use function is_string;

/**
 * Stateless Resource client - coroutine safe
 *
 * This class is stateless and can be safely shared across coroutines.
 * For fluent interface usage ($resource->get->uri()), a new Resource
 * instance is created to support the mutable fluent interface pattern.
 *
 * Not bound by default in 1.x (ResourceInterface defaults to Resource).
 * Async modules (e.g. Swoole Module) override the binding to this class.
 * Coverage is provided by the consuming module's test suite.
 *
 * @psalm-import-type Query from Types
 * @codeCoverageIgnore
 */
final class ResourceClient implements ResourceInterface
{
    /**
     * @param FactoryInterface                   $factory        Resource factory
     * @param InvokerInterface                   $invoker        Resource request invoker
     * @param AnchorInterface                    $anchor         Resource anchor
     * @param ProviderInterface<LinkerInterface> $linkerProvider Resource linker provider
     * @param UriFactory                         $uri            URI factory
     */
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly InvokerInterface $invoker,
        private readonly AnchorInterface $anchor,
        #[Set(LinkerInterface::class)]
        private readonly ProviderInterface $linkerProvider,
        private readonly UriFactory $uri,
    ) {
    }

    /**
     * Delegate to Resource for fluent interface support
     */
    public function __get(string $name): Resource
    {
        return $this->resource()->__get($name);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function newInstance($uri): ResourceObject
    {
        if (is_string($uri)) {
            $uri = ($this->uri)($uri);
        }

        return $this->factory->newInstance($uri);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function object(ResourceObject $ro): RequestInterface
    {
        return new Request($this->invoker, $ro, Method::GET);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function uri($uri): RequestInterface
    {
        return $this->newRequest(Method::GET, (string) $uri);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function newRequest(Method $method, string $uri, array $query = []): RequestInterface
    {
        return $this->resource()->newRequest($method, $uri, $query);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress MixedMethodCall
     */
    #[Override]
    public function crawl(string $uri, string $linkKey, array $query = []): ResourceObject
    {
        /** @var Request $request */
        $request = $this->newRequest(Method::GET, $uri, $query)->linkCrawl($linkKey);
        $request->in = 'eager';
        $ro = $request->request();
        assert($ro instanceof ResourceObject);

        return $ro;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress NoInterfaceProperties
     * @psalm-suppress MixedMethodCall
     */
    #[Override]
    public function href(string $rel, array $query = [], ResourceObject|null $ro = null): ResourceObject
    {
        assert($ro instanceof ResourceObject, 'ResourceObject is required for ResourceClient::href()');
        $sourceRequest = new Request($this->invoker, $ro, Method::from($ro->uri->method), $ro->uri->query);
        [$method, $uri] = $this->anchor->href($rel, $sourceRequest, $query);
        /** @var Request $request */
        $request = $this->newRequest(Method::from($method), $uri, $query);
        $request->in = 'eager';
        $result = $request->request();
        assert($result instanceof ResourceObject);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function get(string $uri, array $query = []): ResourceObject
    {
        return $this->newRequest(Method::GET, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function post(string $uri, array $query = []): ResourceObject
    {
        return $this->newRequest(Method::POST, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function put(string $uri, array $query = []): ResourceObject
    {
        return $this->newRequest(Method::PUT, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function patch(string $uri, array $query = []): ResourceObject
    {
        return $this->newRequest(Method::PATCH, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function delete(string $uri, array $query = []): ResourceObject
    {
        return $this->newRequest(Method::DELETE, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function options(string $uri, array $query = []): ResourceObject
    {
        return $this->newRequest(Method::OPTIONS, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function head(string $uri, array $query = []): ResourceObject
    {
        return $this->newRequest(Method::HEAD, $uri)($query);
    }

    /** A Resource per call keeps this client stateless */
    private function resource(): Resource
    {
        return new Resource($this->factory, $this->invoker, $this->anchor, $this->linkerProvider->get(), $this->uri);
    }
}

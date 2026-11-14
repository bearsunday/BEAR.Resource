<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;

use function array_merge;
use function assert;
use function is_string;

/**
 * Resource client with mutable state - supports fluent interface
 *
 * This class maintains internal state for the fluent interface pattern.
 * It is NOT coroutine-safe due to mutable $method and $request properties.
 * For coroutine-safe usage, use ResourceClient instead.
 *
 * @property $this $get
 * @property $this $post
 * @property $this $put
 * @property $this $patch
 * @property $this $delete
 * @property $this $head
 * @property $this $options
 * @psalm-import-type Query from Types
 */
final class Resource implements ResourceInterface
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private Request $request;
    private string $method = 'get';

    /**
     * @param FactoryInterface $factory Resource factory
     * @param InvokerInterface $invoker Resource request invoker
     * @param AnchorInterface  $anchor  Resource anchor
     * @param LinkerInterface  $linker  Resource linker
     * @param UriFactory       $uri     URI factory
     *
     * @psalm-pure
     */
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly InvokerInterface $invoker,
        private readonly AnchorInterface $anchor,
        private readonly LinkerInterface $linker,
        private readonly UriFactory $uri,
    ) {
    }

    /**
     * Set HTTP method for fluent interface
     *
     * @psalm-capabilities read-props|write-this-props
     */
    public function __get(string $name): self
    {
        $this->method = $name;

        return $this;
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
     *
     * @psalm-capabilities read-props
     */
    #[Override]
    public function object(ResourceObject $ro): RequestInterface
    {
        return new Request($this->invoker, $ro, Method::from($this->method));
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function uri($uri): RequestInterface
    {
        $method = $this->method; // save method, this may change on newInstance(), this is singleton!
        $this->method = 'get';
        $ro = $this->newInstance($uri);
        $ro->uri->method = $method;
        $this->request = new Request($this->invoker, $ro, Method::from($ro->uri->method), $ro->uri->query, [], $this->linker);

        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function newRequest(Method $method, string $uri, array $query = []): RequestInterface
    {
        $ro = $this->newInstance($uri);
        $ro->uri->method = $method->value;
        $ro->uri->query = array_merge($ro->uri->query, $query);
        $this->request = new Request($this->invoker, $ro, $method, $ro->uri->query, [], $this->linker);

        return $this->request;
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
        $sourceRequest = $ro !== null
            ? new Request($this->invoker, $ro, Method::from($ro->uri->method), $ro->uri->query)
            : $this->request;
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
        return $this->methodUri(Method::GET, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function post(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Method::POST, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function put(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Method::PUT, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function patch(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Method::PATCH, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function delete(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Method::DELETE, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function options(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Method::OPTIONS, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function head(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Method::HEAD, $uri)($query);
    }

    private function methodUri(Method $method, string $uri): RequestInterface
    {
        $this->method = $method->value;

        return $this->uri($uri);
    }
}

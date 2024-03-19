<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\MethodException;

use function assert;
use function is_string;

/**
 * @property $this $get
 * @property $this $post
 * @property $this $put
 * @property $this $patch
 * @property $this $delete
 * @property $this $head
 * @property $this $options
 */
final class Resource implements ResourceInterface
{
    /** @psalm-suppress PropertyNotSetInConstructor */
    private Request $request;
    private string $method = 'get';
    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param FactoryInterface $factory Resource factory
     * @param InvokerInterface $invoker Resource request invoker
     * @param AnchorInterface  $anchor  Resource anchor
     * @param LinkerInterface  $linker  Resource linker
     * @param UriFactory       $uri     URI factory
     */
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly InvokerInterface $invoker,
        private readonly AnchorInterface $anchor,
        private readonly LinkerInterface $linker,
        private readonly UriFactory $uri,
    ) {
    }

    public function __get(string $name): self
    {
        $this->method = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
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
     * @throws MethodException
     */
    public function object(ResourceObject $ro): RequestInterface
    {
        return new Request($this->invoker, $ro, $this->method);
    }

    /**
     * {@inheritDoc}
     */
    public function uri($uri): RequestInterface
    {
        $method = $this->method; // save method, this may change on newInstance(), this is singleton!
        $this->method = 'get';
        $ro = $this->newInstance($uri);
        $ro->uri->method = $method;
        $this->request = new Request($this->invoker, $ro, $ro->uri->method, $ro->uri->query, [], $this->linker);

        return $this->request;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MixedPropertyFetch
     */
    public function href(string $rel, array $query = []): ResourceObject
    {
        [$method, $uri] = $this->anchor->href($rel, $this->request, $query);
        /** @psalm-suppress MixedMethodCall */
        $resourceObject = $this->{$method}->uri($uri)->addQuery($query)->eager->request();
        assert($resourceObject instanceof ResourceObject);

        return $resourceObject;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Request::GET, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    public function post(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Request::POST, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    public function put(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Request::PUT, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    public function patch(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Request::PATCH, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Request::DELETE, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    public function options(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Request::OPTIONS, $uri)($query);
    }

    /**
     * {@inheritDoc}
     */
    public function head(string $uri, array $query = []): ResourceObject
    {
        return $this->methodUri(Request::HEAD, $uri)($query);
    }

    private function methodUri(string $method, string $uri): RequestInterface
    {
        $this->method = $method;

        return $this->uri($uri);
    }
}

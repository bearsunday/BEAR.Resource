<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * @property-read $this $get
 * @property-read $this $post
 * @property-read $this $put
 * @property-read $this $patch
 * @property-read $this $delete
 * @property-read $this $head
 * @property-read $this $options
 */
final class Resource implements ResourceInterface
{
    /**
     * Resource factory
     *
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * Anchor
     *
     * @var AnchorInterface
     */
    private $anchor;

    /**
     * Linker
     *
     * @var LinkerInterface
     */
    private $linker;

    /**
     * Request
     *
     * @var Request
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $request;

    /**
     * Request method
     *
     * @var string
     */
    private $method = 'get';

    /**
     * @var UriFactory
     */
    private $uri;

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param FactoryInterface $factory Resource factory
     * @param InvokerInterface $invoker Resource request invoker
     * @param AnchorInterface  $anchor  Resource anchor
     * @param LinkerInterface  $linker  Resource linker
     * @param UriFactory       $uri     URI factory
     */
    public function __construct(
        FactoryInterface $factory,
        InvokerInterface $invoker,
        AnchorInterface $anchor,
        LinkerInterface $linker,
        UriFactory $uri
    ) {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->anchor = $anchor;
        $this->linker = $linker;
        $this->uri = $uri;
    }

    public function __get(string $name) : self
    {
        $this->method = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance($uri) : ResourceObject
    {
        if (is_string($uri)) {
            $uri = ($this->uri)($uri);
        }

        return $this->factory->newInstance($uri);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \BEAR\Resource\Exception\MethodException
     */
    public function object(ResourceObject $ro) : RequestInterface
    {
        return new Request($this->invoker, $ro, $this->method);
    }

    /**
     * {@inheritdoc}
     */
    public function uri($uri) : RequestInterface
    {
        $method = $this->method; // save method, this may change on newInstance(), this is singleton!
        $this->method = 'get';
        $ro = $this->newInstance($uri);
        $ro->uri->method = $method;
        $this->request = new Request($this->invoker, $ro, $ro->uri->method, $ro->uri->query, [], $this->linker);

        return $this->request;
    }

    /**
     * {@inheritdoc}
     *
     * @psalm-suppress MixedPropertyFetch
     */
    public function href(string $rel, array $query = []) : ResourceObject
    {
        [$method, $uri] = $this->anchor->href($rel, $this->request, $query);
        /** @psalm-suppress MixedMethodCall */
        $resourceObject = $this->{$method}->uri($uri)->addQuery($query)->eager->request();
        assert($resourceObject instanceof ResourceObject);

        return $resourceObject;
    }

    public function get(string $uri, array $query = []) : ResourceObject
    {
        return $this->methodUri(Request::GET, $uri)($query);
    }

    public function post(string $uri, array $query = []) : ResourceObject
    {
        return $this->methodUri(Request::POST, $uri)($query);
    }

    public function put(string $uri, array $query = []) : ResourceObject
    {
        return $this->methodUri(Request::PUT, $uri)($query);
    }

    public function patch(string $uri, array $query = []) : ResourceObject
    {
        return $this->methodUri(Request::PATCH, $uri)($query);
    }

    public function delete(string $uri, array $query = []) : ResourceObject
    {
        return $this->methodUri(Request::DELETE, $uri)($query);
    }

    public function options(string $uri, array $query = []) : ResourceObject
    {
        return $this->methodUri(Request::OPTIONS, $uri)($query);
    }

    public function head(string $uri, array $query = []) : ResourceObject
    {
        return $this->methodUri(Request::HEAD, $uri)($query);
    }

    private function methodUri(string $method, string $uri) : RequestInterface
    {
        $this->method = $method;

        return $this->uri($uri);
    }
}

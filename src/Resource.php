<?php

declare(strict_types=1);

namespace BEAR\Resource;

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
        AnchorInterface  $anchor,
        LinkerInterface  $linker,
        UriFactory $uri
    ) {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->anchor = $anchor;
        $this->linker = $linker;
        $this->uri = $uri;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function __get($name)
    {
        $this->method = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function newInstance($uri) : ResourceObject
    {
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
        if (is_string($uri)) {
            $uri = ($this->uri)($uri);
        }
        $uri->method = $this->method;
        $ro = $this->newInstance($uri);
        $ro->uri = $uri;
        $this->request = new Request($this->invoker, $ro, $uri->method, $uri->query, [], $this->linker);
        $this->method = 'get';

        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function href(string $rel, array $query = []) : ResourceObject
    {
        list($method, $uri) = $this->anchor->href($rel, $this->request, $query);

        return $this->{$method}->uri($uri)->addQuery($query)->eager->request();
    }

    public function get(string $uri, array $query = []) : ResourceObject
    {
        $this->method = Request::GET;

        ($this->uri)($uri);

        return $this->uri(($this->uri)($uri))($query);
    }

    public function post(string $uri, array $query = []) : ResourceObject
    {
        $this->method = Request::POST;

        return $this->uri(($this->uri)($uri))($query);
    }

    public function put(string $uri, array $query = []) : ResourceObject
    {
        $this->method = Request::PUT;

        return $this->uri(($this->uri)($uri))($query);
    }

    public function patch(string $uri, array $query = []) : ResourceObject
    {
        $this->method = Request::PATCH;

        return $this->uri(($this->uri)($uri))($query);
    }

    public function delete(string $uri, array $query = []) : ResourceObject
    {
        $this->method = Request::DELETE;

        return $this->uri(($this->uri)($uri))($query);
    }

    public function options(string $uri, array $query = []) : ResourceObject
    {
        $this->method = Request::OPTIONS;

        return $this->uri(($this->uri)($uri))($query);
    }

    public function head(string $uri, array $query = []) : ResourceObject
    {
        $this->method = Request::HEAD;

        return $this->uri(($this->uri)($uri))($query);
    }
}

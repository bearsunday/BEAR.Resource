<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Resource;

/**
 * @property $this $get
 * @property $this $post
 * @property $this $patch
 * @property $this $put
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

    /** @noinspection MoreThanThreeArgumentsInspection */

    /**
     * @param FactoryInterface $factory Resource factory
     * @param InvokerInterface $invoker Resource request invoker
     * @param AnchorInterface  $anchor  Resource anchor
     * @param LinkerInterface  $linker  Resource linker
     */
    public function __construct(
        FactoryInterface $factory,
        InvokerInterface $invoker,
        AnchorInterface  $anchor,
        LinkerInterface  $linker
    ) {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->anchor = $anchor;
        $this->linker = $linker;
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
            $uri = new Uri($uri);
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
}

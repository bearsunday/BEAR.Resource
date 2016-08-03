<?php
/**
 * This file is part of the BEAR.Resource package
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
    private $method;

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
     * {@inheritDoc}
     */
    public function newInstance($uri)
    {
        return $this->factory->newInstance($uri);
    }

    /**
     * {@inheritDoc}
     * @throws \BEAR\Resource\Exception\MethodException
     */
    public function object($resourceObject)
    {
        return new Request(
            $this->invoker,
            $resourceObject,
            $this->method
        );
    }

    /**
     * {@inheritDoc}
     * @throws \BEAR\Resource\Exception\MethodException
     * @throws \BEAR\Resource\Exception\UriException
     */
    public function uri($uri)
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }
        $resourceObject = $this->newInstance($uri);
        $resourceObject->uri = $uri;
        $this->request = new Request(
            $this->invoker,
            $resourceObject,
            $this->method,
            $uri->query,
            [],
            $this->linker
        );

        return $this->request;
    }

    /**
     * {@inheritDoc}
     * @throws \BEAR\Resource\Exception\MethodException
     */
    public function href($rel, array $query = [])
    {
        list($method, $uri) = $this->anchor->href($rel, $this->request, $query);
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $target = $this->{$method}->uri($uri)->addQuery($query)->eager->request();

        return $target;
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
}

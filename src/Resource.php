<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

/**
 * @property $this $get
 * @property $this $post
 * @property $this $put
 * @property $this $head
 * @property $this $options
 */
final class Resource implements ResourceInterface
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var Invoker
     */
    private $invoker;

    /**
     * @var Anchor
     */
    private $anchor;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param FactoryInterface $factory
     * @param InvokerInterface $invoker
     * @param AnchorInterface  $anchor
     */
    public function __construct(
        FactoryInterface $factory,
        InvokerInterface $invoker,
        AnchorInterface  $anchor
    ) {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->anchor = $anchor;
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
            $uri->query
        );

        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function href($rel, array $query = [])
    {
        list($method, $uri) = $this->anchor->href($rel, $this->request, $query);
        $target = $this->{$method}->uri($uri)->eager->request();

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

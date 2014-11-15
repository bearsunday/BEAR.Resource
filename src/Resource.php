<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

class Resource implements ResourceInterface
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
     * @var ResourceObject
     */
    private $resourceObject;

    /**
     * [method, eager|lazy, links]
     *
     * @var string
     */
    private $method = '';

    /**
     * @var string
     */
    private $when = 'lazy';

    /**
     * @var array
     */
    private $query = [];

    /**
     * @var
     */
    private $links = [];

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
        $this->resourceObject = $resourceObject;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function uri($uri)
    {
        if (is_string($uri)) {
            $uri = new Uri($uri);
        }
        $this->resourceObject = $this->newInstance($uri);
        $this->query = $uri->query;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery(array $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addQuery(array $query)
    {
        $this->query = $query + $this->query;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkSelf($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::SELF_LINK);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkNew($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::NEW_LINK);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkCrawl($linkKey)
    {
        $this->links[] = new LinkType($linkKey, LinkType::CRAWL_LINK);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function request()
    {
        $method = $this->method ?: Request::GET;
        $this->request = new Request(
            $this->invoker,
            $this->resourceObject,
            $method,
            $this->query,
            $this->links
        );
        $result = ($this->when === 'eager') ? $this->invoke($this->request) : $this->request;
        $this->query = [];
        $this->method = $this->when = '';

        return $result;
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
     * @param Request $request
     *
     * @return ResourceObject
     */
    private function invoke($request)
    {
        return $this->invoker->invoke($request);
    }


    /**
     * @param string $name
     *
     * @return $this
     */
    public function __get($name)
    {
        if ($this->method === '') {
            $this->method = $name;

            return $this;
        }
        $this->when = $name;

        return $this;
    }
}

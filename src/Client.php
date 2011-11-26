<?php
/**
 * BEAR.Resource
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Resource client
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @Scope("singleton")
 */
class Client implements Resource
{
    /**
     * Resource factory
     *
     * @var Factory
     */
    private $factory;

    /**
     * Resource request invoker
     *
     * @var Invoker
     */
    private $invoker;

    /**
     * Resource request
     *
     * @var Request
     */
    private $request;

    /**
     * Constructor
     *
     * @param Factory $factory resource object factory.
     * @param Invokable  $invoker resource request invoker
     * @param Request $request resource request
     *
     * @Inject
     */
    public function __construct(Factory $factory, Invokable $invoker, Request $request)
    {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->newRequest = $request;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::newInstance()
     */
    public function newInstance($uri)
    {
        $instance = $this->factory->newInstance($uri);
        return $instance;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::object()
     */
    public function object($ro)
    {
        $this->request->ro = $ro;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::uri()
     */
    public function uri($uri)
    {
        $this->request->ro = $this->newInstance($uri);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::withQuery()
     */
    public function withQuery(array $query)
    {
        $this->request->query = $query;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::linkSelf()
     */
    public function linkSelf($linkKey)
    {
        $link = new Link();
        $link->key = $linkKey;
        $link->type = Link::SELF_LINK;
        $this->request->links[] = $link;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::linkNew()
     */
    public function linkNew($linkKey)
    {
        $link = new Link();
        $link->key = $linkKey;
        $link->type = Link::NEW_LINK;
        $this->request->links[] = $link;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::linkCrawl()
     */
    public function linkCrawl($linkKey)
    {
        $link = new Link();
        $link->key = $linkKey;
        $link->type = Link::CRAWL_LINK;
        $this->request->links[] = $link;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::request()
     */
    public function request()
    {
        if ($this->request->in === 'eager') {
            return $this->invoker->invoke($this->request);
        }
        return $this->request;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::__get($name)
     * @throws Exception\InvalidRequest
     */
    public function __get($name)
    {
        switch ($name) {
            case 'get':
            case 'post':
            case 'put':
            case 'delete':
            case 'head':
                $this->request = clone $this->newRequest;
                $this->request->method = $name;
                return $this;
            case 'lazy':
            case 'eager':
                $this->request->in = $name;
                return $this;
            case 'poe':
            case 'csrf':
                $this->request->options[$name] = true;
                return $this;
            default:
                throw new Exception\InvalidRequest($name);
        }
    }

    /**
     * Return requeset string
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->request;
    }

}

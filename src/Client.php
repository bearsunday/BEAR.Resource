<?php
/**
 * BEAR.Resource
 *
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Object as ResourceObject;

/**
 * Resource client
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Client implements Resource
{
    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor
     *
     * @param Factory $factory resource object factory.
     * @param Invoke  $invoker resource object invoker
     * @param Request $request resource request
     *
     * @Inject
     */
    public function __construct(Factory $factory, Invoke $invoker, Request $request)
    {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->newRequest = $request;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::newInstance()
     * @return Client
     */
    public function newInstance($uri, array $query = array())
    {
        $instance = $this->factory->newInstance($uri, $query);
        return $instance;
    }

    /**
     * Set resource objcet
     *
     * @param ResourceObject $ro
     * @return Client
     */
    public function object($ro)
    {
        $this->request->ro = $ro;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::uri()
     * @return Client
     */
    public function uri($uri)
    {
        $this->request->ro = $this->newInstance($uri);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::withQuery()
     * @return Client
     */
    public function withQuery(array $query)
    {
        $this->request->query = $query;
        return $this;
    }

    public function linkSelf($linkKey)
    {
        $link = new Link();
        $link->key = $linkKey;
        $link->type = Link::SELF_LINK;
        $this->request->links[] = $link;
        return $this;
    }

    public function linkNew($linkKey)
    {
        $link = new Link();
        $link->key = $linkKey;
        $link->type = Link::NEW_LINK;
        $this->request->links[] = $link;
        return $this;
    }

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
     * @return \BEAR\Resource\Client
     */
    public function request()
    {
        if ($this->request->in === 'eager') {
            return $this->invoker->invoke($this->request);
        }
        return $this->request;
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

    /**
     * Set options parameter
     *
     * @param string $attribute
     *
     * @return \BEAR\Resource\Client
     * @throw  \BEAR\Resource\Exception\InvalidParameter
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
}

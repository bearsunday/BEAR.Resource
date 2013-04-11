<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Exception;
use BEAR\Resource\Uri;
use Guzzle\Cache\CacheAdapterInterface;
use BEAR\Resource\SignalHandler\HandleInterface;
use Ray\Di\Di\Scope;
use SplObjectStorage;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Resource client
 *
 * @package BEAR.Resource
 * @SuppressWarnings(PHPMD.TooManyMethods)
 *
 * @Scope("singleton")
 */
class Resource implements ResourceInterface
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
     * Requests
     *
     * @var \SplObjectStorage
     */
    private $requests;

    /**
     * Cache
     *
     * @var CacheAdapterInterface
     */
    private $cache;


    /**
     * Set cache adapter
     *
     * @param CacheAdapterInterface $cache
     *
     * @Inject(optional = true)
     * @Named("resource_cache")
     */
    public function setCacheAdapter(CacheAdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Set scheme collection
     *
     * @param SchemeCollection $scheme
     *
     * @Inject(optional = true)
     */
    public function setSchemeCollection(SchemeCollection $scheme)
    {
        $this->factory->setSchemeCollection($scheme);
    }

    /**
     * Constructor
     *
     * @param Factory          $factory resource object factory
     * @param InvokerInterface $invoker resource request invoker
     * @param Request          $request resource request
     *
     * @Inject
     */
    public function __construct(Factory $factory, InvokerInterface $invoker, Request $request)
    {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->newRequest = $request;
        $this->requests = new SplObjectStorage;
        $this->invoker->setResourceClient($this);
    }

    /**
     * {@inheritDoc}
     */
    public function newInstance($uri)
    {
        if (substr($uri, -1) === '/') {
            $uri .= 'index';
        }
        $useCache = $this->cache instanceof CacheAdapterInterface;
        if ($useCache === true) {
            $key = 'res-' . str_replace('/', '-', $uri);
            $cached = $this->cache->fetch($key);
            if ($cached) {
                return $cached;
            }
        }
        $instance = $this->factory->newInstance($uri);
        if ($useCache === true) {
            /** @noinspection PhpUndefinedVariableInspection */
            $this->cache->save($key, $instance);
        }

        return $instance;
    }

    /**
     * {@inheritDoc}
     */
    public function object($ro)
    {
        $this->request->ro = $ro;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function uri($uri)
    {
        if (is_string($uri)) {
            if (!$this->request) {
                throw new Exception\BadRequest('Request method (get/put/post/delete/options) required before uri()');
            }
            if (!preg_match('|^[a-z]+?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $uri)) {
                throw new Exception\Uri($uri);
            }
            // uri with query parsed
            if (strpos($uri, '?') !== false) {
                $parsed = parse_url($uri);
                $uri = $parsed['scheme'] . '://' . $parsed['host'] . $parsed['path'];
                if (isset($parsed['query'])) {
                    parse_str($parsed['query'], $query);
                    $this->withQuery($query);
                }
            }
            $this->request->ro = $this->newInstance($uri);
            $this->request->uri = $uri;

            return $this;
        }
        if ($uri instanceof Uri) {
            $this->request->ro = $this->newInstance($uri->uri);
            $this->withQuery($uri->query);

            return $this;
        }
        throw new Exception\Uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery(array $query)
    {
        $this->request->query = $query;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addQuery(array $query)
    {
        $this->request->query = array_merge($this->request->query, $query);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkSelf($linkKey)
    {
        $link = new LinkType;
        $link->key = $linkKey;
        $link->type = LinkType::SELF_LINK;
        $this->request->links[] = $link;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkNew($linkKey)
    {
        $link = new LinkType;
        $link->key = $linkKey;
        $link->type = LinkType::NEW_LINK;
        $this->request->links[] = $link;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkCrawl($linkKey)
    {
        $link = new LinkType;
        $link->key = $linkKey;
        $link->type = LinkType::CRAWL_LINK;
        $this->request->links[] = $link;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function request()
    {
        $this->request->ro->uri = $this->request->toUri();
        if (isset($this->request->options['sync'])) {
            $this->requests->attach($this->request);
            $this->request = clone $this->newRequest;

            return $this;
        }
        if ($this->request->in === 'eager') {
            if ($this->requests->count() === 0) {
                $result = $this->invoker->invoke($this->request);
            } else {
                $this->requests->attach($this->request);
                $result = $this->invoker->invokeSync($this->requests);
            }
            if (!($result instanceof ObjectInterface) && isset($this->request->ro)) {
                $this->request->ro->body = $result;
                $result = $this->request->ro;
            }

            return $result;
        }

        // logs
        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function attachParamProvider($signal, HandleInterface $argProvider)
    {
        /** @noinspection PhpParamsInspection */
        $this->invoker->getSignal()->handler(
            '\BEAR\Resource',
            Invoker::SIGNAL_PARAM . $signal,
            $argProvider
        );
    }

    /**
     * {@inheritDoc}
     * @throws Exception\Request
     */
    public function __get($name)
    {
        switch (true) {
            case (in_array($name, ['get', 'post', 'put', 'delete', 'head', 'options'])):
                $this->request = clone $this->newRequest;
                $this->request->method = $name;

                return $this;
            case (in_array($name, ['lazy', 'eager'])):
                $this->request->in = $name;

                return $this;
            case (in_array($name, ['sync', 'poe', 'csrf'])):
                $this->request->options[$name] = true;

                return $this;
            default:
                throw new Exception\BadRequest($name, 400);
        }
    }

    /**
     * Return request string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->request->toUri();
    }
}

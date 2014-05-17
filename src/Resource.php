<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Doctrine\Common\Cache\Cache;
use SplObjectStorage;
use Ray\Di\Di\Scope;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Traversable;

/**
 * Resource client
 *
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
     * @var Cache
     */
    private $cache;

    /**
     * @var string
     */
    private $appName = '';

    /**
     * @var Anchor
     */
    private $anchor;

    /**
     * @var Request
     */
    private $newRequest;

    /**
     * @var ResourceObject[]
     */
    private $resourceObjects;

    /**
     * @param $appName
     *
     * @Inject(optional = true)
     * @Named("app_name")
     *
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * Set cache adapter
     *
     * @param Cache $cache
     *
     * @Inject(optional = true)
     * @Named("resource_cache")
     */
    public function setCacheAdapter(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Set scheme collection
     *
     * @param SchemeCollectionInterface $scheme
     *
     * @Inject(optional = true)
     */
    public function setSchemeCollection(SchemeCollectionInterface $scheme)
    {
        $this->factory->setSchemeCollection($scheme);
    }

    /**
     * @param Factory          $factory resource object factory
     * @param InvokerInterface $invoker resource request invoker
     * @param Request          $request resource request
     * @param Anchor           $anchor  resource linker
     *
     * @Inject
     */
    public function __construct(
        Factory $factory,
        InvokerInterface $invoker,
        Request $request,
        Anchor $anchor
    ) {
        $this->factory = $factory;
        $this->invoker = $invoker;
        $this->newRequest = $request;
        $this->requests = new SplObjectStorage;
        $this->invoker->setResourceClient($this);
        $this->anchor = $anchor;
    }

    /**
     * {@inheritDoc}
     */
    public function newInstance($uri)
    {
        if (isset($this->resourceObjects[$uri])) {
            return clone $this->resourceObjects[$uri];
        }

        $useCache = $this->cache instanceof Cache;
        $key = $this->appName . 'res-' . str_replace('/', '-', $uri);
        if ($useCache === true) {
            $cached = $this->cache->fetch($key);
            if ($cached) {
                return $cached;
            }
        }
        $instance = $this->factory->newInstance($uri);
        if ($useCache === true) {
            $this->cache->save($key, $instance);
        }
        $this->resourceObjects[$uri] = $instance;

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
        if ($uri instanceof Uri) {
            $this->request->ro = $this->newInstance($uri->uri);
            $this->withQuery($uri->query);

            return $this;
        }
        if (! $this->request) {
            throw new Exception\BadRequest('Request method (get/put/post/delete/options) required before uri()');
        }
        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
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
        $this->request->links[] = new LinkType($linkKey, LinkType::SELF_LINK);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkNew($linkKey)
    {
        $this->request->links[] = new LinkType($linkKey, LinkType::NEW_LINK);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function linkCrawl($linkKey)
    {
        $this->request->links[] = new LinkType($linkKey, LinkType::CRAWL_LINK);

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
        if ($this->request->in !== 'eager') {
            return clone $this->request;
        }

        return $this->invoke();
    }

    public function href($rel, array $query = [])
    {
        list($method, $uri) = $this->anchor->href($rel, $this->request, $query);
        $linkedResource = $this->{$method}->uri($uri)->eager->request();

        return $linkedResource;
    }

    /**
     * @return ResourceObject|mixed
     */
    private function invoke()
    {
        if ($this->requests->count() === 0) {
            return $this->invoker->invoke($this->request);
        }
        $this->requests->attach($this->request);

        return $this->invoker->invokeSync($this->requests);
    }

    /**
     * {@inheritDoc}
     */
    public function attachParamProvider($varName, ParamProviderInterface $provider)
    {
        $this->invoker->attachParamProvider($varName, $provider);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExceptionHandler(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->invoker->setExceptionHandler($exceptionHandler);
    }

    /**
     * {@inheritDoc}
     * @throws Exception\Request
     */
    public function __get($name)
    {
        if (in_array($name, ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'])) {
            $this->request = clone $this->newRequest;
            $this->request->method = $name;

            return $this;
        }
        if (in_array($name, ['lazy', 'eager'])) {
            $this->request->in = $name;

            return $this;
        }
        if (in_array($name, ['sync'])) {
            $this->request->options[$name] = $name;

            return $this;
        }
        throw new Exception\BadRequest($name, 400);
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

    /**
     * {@inheritdoc}
     *
     * @return \ArrayIterator|\MultipleIterator|Traversable
     */
    public function getIterator()
    {
        return $this->factory->getIterator();
    }
}

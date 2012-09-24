<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use BEAR\Resource\Adapter\App\Link as LikType;
use BEAR\Resource\Exception\BadRequest;
use BEAR\Resource\Exception;
use BEAR\Resource\Exception\InvalidUri;
use Guzzle\Common\Cache\AbstractCacheAdapter as Cache;
use Ray\Di\Di\Inject;

/**
 * Resource client
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
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
     * @var Guzzle\Common\Cache\CacheAdapterInterface
     */
    private $cache;

    /**
     * Resource requeset log
     *
     * @var array
     */
    private $logs = [];

    /**
     * Constructor
     *
     * @param Factory          $factory resource object factory.
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
        $this->requests = new \SplObjectStorage;
        $this->invoker->setResourceClient($this);
    }

    /**
     * Set cache adapter
     *
     * @param Cache $cache
     *
     * @Inject(optional = true)
     */
    public function setCacheAdapter(Cache $cache)
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
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::newInstance()
     */
    public function newInstance($uri)
    {
        if (substr($uri, -1) === '/') {
            $uri .= 'index';
        }
        $useCache = $this->cache instanceof Cache;
        if ($useCache === true) {
            $key = '(Resource) ' . $uri;
            $cached = $this->cache->fetch($key);
            if ($cached) {
                return $cached;
            }
        }
        $instance = $this->factory->newInstance($uri);
        if ($useCache === true) {
            try {
                $this->cache->save($key, $instance);
            } catch (\Exception $e) {
                $msg = "resource({$uri}) is not stored in cache";
                if (PHP_SAPI !== 'cli') {
                    error_log($msg);
                }
            }
        }

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
        if (is_string($uri)) {
            if (! $this->request) {
                throw new BadRequest('Request method (get/put/post/delete/options) required before uri()');
            }
            if (! preg_match('|^[a-z]+?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $uri)) {
                throw new Exception\InvalidUri($uri);
            }
            // uri with query parsed
            if (strpos($uri, '?') !== false) {
                $parsed = parse_url($uri);
                $uri = $parsed['scheme'] . '://' .  $parsed['host'] .  $parsed['path'];
                parse_str($parsed['query'], $query);
                $this->withQuery($query);
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
        throw new Exception\InvalidUri;
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
     * @see BEAR\Resource.Resource::addQuery()
     */
    public function addQuery(array $query)
    {
        $this->request->query = array_merge($this->request->query, $query);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::linkSelf()
     */
    public function linkSelf($linkKey)
    {
        $link = new LikType;
        $link->key = $linkKey;
        $link->type = LikType::SELF_LINK;
        $this->request->links[] = $link;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::linkNew()
     */
    public function linkNew($linkKey)
    {
        $link = new LikType;
        $link->key = $linkKey;
        $link->type = LikType::NEW_LINK;
        $this->request->links[] = $link;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::linkCrawl()
     */
    public function linkCrawl($linkKey)
    {
        $link = new LikType;
        $link->key = $linkKey;
        $link->type = LikType::CRAWL_LINK;
        $this->request->links[] = $link;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::request()
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
            if (!($result instanceof Object) && isset($this->request->ro)) {
                $this->request->ro->body = $result;
                $result = $this->request->ro;
            }

            return $result;
        }
        // logs
        return $this->request;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource.Resource::attachParamProvider()
     */
    public function attachParamProvider($signal, Callable $argProvider)
    {
        $this->invoker->getSignal()->handler(
                '\BEAR\Resource\Invoker',
                \BEAR\Resource\Invoker::SIGNAL_PARAM . $signal,
                $argProvider
        );
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
            case 'options':
                $this->request = clone $this->newRequest;
                $this->request->method = $name;

                return $this;
            case 'lazy':
            case 'eager':
                $this->request->in = $name;

                return $this;
            case 'poe':
            case 'csrf':
            case 'sync':
                $this->request->options[$name] = true;

                return $this;
            default:
                throw new Exception\BadRequest($name, 400);
        }
    }

    /**
     * Return requeset string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->request->toUri();
    }

}

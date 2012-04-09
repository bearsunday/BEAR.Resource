<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource;

use Aura\Autoload\Exception\NotReadable;
use BEAR\Resource\Object as ResourceObject;
use BEAR\Resource\Adapter\App\Link as LikType;
use BEAR\Resource\Exception;
use BEAR\Resource\Exception\ResourceNotFound;
use Guzzle\Common\Cache\AbstractCacheAdapter as Cache;
use ReflectionException;

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
        $this->requests = new \SplObjectStorage;
    }

    /**
     * Set cache adapter
     *
     * @param Cache $cache
     *
     * @Inject
     */
    public function setCacheAdapter(Cache $cache)
    {
        $this->cache = $cache;
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
        if ($this->cache instanceof Cache) {
            $key = __METHOD__ . $uri;
            $cached = $this->cache->fetch($key);
            if ($cached) {
                return $cached;
            }
        }
        try {
            $instance = $this->factory->newInstance($uri);
        } catch (NotReadable $e) {
            throw new ResourceNotFound($uri, 400, $e);
        } catch (ReflectionException $e) {
            throw new ResourceNotFound($uri, 400, $e);
        }
        if ($this->cache instanceof Cache) {
            $this->cache->save($key, $instance);
        }
        if (method_exists($instance, '__wakeup')) {
            $instance->__wakeup();
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
            $this->request->ro = $this->newInstance($uri);
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
        if (isset($this->request->options['sync'])) {
            $this->requests->attach($this->request);
            $this->request = clone $this->newRequest;
            return $this;
        }
        if ($this->request->in === 'eager') {
            if ($this->requests->count() === 0) {
                return $this->invoker->invoke($this->request);
            } else {
                $this->requests->attach($this->request);
                return $this->invoker->invokeSync($this->requests);
            }
        }
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

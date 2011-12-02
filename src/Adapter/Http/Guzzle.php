<?php
/**
 * BEAR.Resource
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\Http;

use Ray\Di\InjectorInterface,
    BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\Provider,
    BEAR\Resource\Exception,
    BEAR\Resource\Linkable;

use Guzzle\Service\Client as GuzzleClient,
    Guzzle\Common\Cache\DoctrineCacheAdapter,
    Guzzle\Http\Plugin\CachePlugin;

use Doctrine\Common\Cache\ApcCache,
    Doctrine\Common\Cache\ArrayCache;

/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 *
 * @Scope("singleton")
 */
class Guzzle implements ResourceObject, HttpClient
{

    public $response;

    public function __construct(GuzzleClient $guzzle)
    {
        $this->guzzle = $guzzle;
        $cacheAdapter = (function_exists('apc_cache_info')) ? 'Doctrine\Common\Cache\ApcCache' : 'Doctrine\Common\Cache\ArrayCache';
        $adapter = new DoctrineCacheAdapter(new $cacheAdapter());
        $cache = new CachePlugin($adapter, true);
        $this->guzzle->getEventManager()->attach($cache);
    }

    /**
     * Get
     *
     * @return mixed
     * @Get
     */
    public function onGet()
    {
        $this->response = $response = $this->guzzle->get()->send();
        $this->setResponse();
        return $this;
    }

    /**
     * Post
     *
     * @return mixed
     * @Post
     */
    public function onPost()
    {
        $this->response = $response = $this->guzzle->post()->send();
        $this->setResponse();
        return $this;
    }

    /**
     * Put
     *
     * @return mixed
     * @Put
     */
    public function onPut()
    {
        $this->response = $response = $this->guzzle->put()->send();
        $this->setResponse();
        return $this;
    }

    /**
     * Delete
     *
     * @return mixed
     * @Delete
     */
    public function onDelete()
    {
        $this->response = $response = $this->guzzle->delete()->send();
        $this->setResponse();
        return $this;
    }

    /**
     * Head
     *
     * @return mixed
     * @Get
     */
    public function onHead()
    {
        $this->response = $response = $this->guzzle->head()->send();
        $this->setResponse();
        return $this;
    }

    /**
     * Options
     *
     * @return mixed
     * @Get
     */
    public function onOptions()
    {
        $this->response = $response = $this->guzzle->options()->send();
        $this->setResponse();
        return $this;
    }

    protected function setResponse()
    {
        /* @var $response \Guzzle\Http\Message\RequestInterface */
        $this->code = $this->response->getStatusCode();
        $headers = $this->response->getHeaders();
        /* @var $headers \Guzzle\Common\Collection */
        $this->headers = $headers->getAll();
        $body = $this->response->getBody(true);
        $format = strpos($headers['Content-Type'], -3, 3);
        if (strpos($headers['Content-Type'], 'xml') !== false) {
            $this->body = new \SimpleXMLElement($body);
        } elseif (strpos($headers['Content-Type'], 'json') !== false) {
            $this->body = json_decode($body);
        } else {
            $this->body = $body;
        }
    }
}
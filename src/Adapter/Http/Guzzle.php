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
    BEAR\Resource\Linkable,
    BEAR\Resource\Request;

use Guzzle\Service\Client as GuzzleClient,
    Guzzle\Common\Cache\DoctrineCacheAdapter,
    Guzzle\Http\Plugin\CachePlugin,
    Guzzle\Http\Message\RequestInterface,
    Guzzle\Http\Message\Response;

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
    /**
     * HTTP Response
     *
     * @var RequestInterface
     */
    private $response;

    /**
     * @var GuzzleClient
     */
    private $guzzle;

    public function __construct(GuzzleClient $guzzle)
    {
        $this->guzzle = $guzzle;
        $cacheAdapter = (function_exists('apc_cache_info')) ? 'Doctrine\Common\Cache\ApcCache' : 'Doctrine\Common\Cache\ArrayCache';
        $adapter = new DoctrineCacheAdapter(new $cacheAdapter());
        $cache = new CachePlugin($adapter, true);
        $this->guzzle->getEventManager()->attach($cache);
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClient::onGet()
     */
    public function onGet()
    {
        $this->response = $response = $this->guzzle->get()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClient::onPost()
     */
    public function onPost()
    {
        $this->response = $response = $this->guzzle->post()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClient::onPut()
     */
    public function onPut()
    {
        $this->response = $response = $this->guzzle->put()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClient::onDelete()
     */
    public function onDelete()
    {
        $this->response = $response = $this->guzzle->delete()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClient::onHead()
     */
    public function onHead()
    {
        $this->response = $response = $this->guzzle->head()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClient::onOptions()
     */
    public function onOptions()
    {
        $this->response = $response = $this->guzzle->options()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);
        return $this;
    }

    /**
     * Parse HTTP response
     *
     * @param Response $response
     */
    protected function parseResponse(Response $response)
    {
        /* @var $response \Guzzle\Http\Message\RequestInterface */
        $code = $response->getStatusCode();
        $headers = $response->getHeaders()->getAll();
        $body = $response->getBody(true);
        if (strpos($headers['Content-Type'], 'xml') !== false && $body) {
            $body = new \SimpleXMLElement($body);
        } elseif (strpos($headers['Content-Type'], 'json') !== false) {
            $body = json_decode($body);
        }
        return array($code, $headers, $body);
    }

    /**
     * Continue Sync
     *
     * @param Request $request
     * @param \ArrayObject $syncData
     *
     * @return void
     */
    public function onSync(Request $request, \ArrayObject $syncData)
    {
        $syncData[] = $request;
    }

    /**
     * Finalise sync
     *
     * @param Request $request
     * @param \ArrayObject $syncData
     *
     * @return \BEAR\Resource\Adapter\Http\Guzzle
     */
    public function onFinalSync(Request $request, \ArrayObject $syncData)
    {
        $batch = array();
        foreach ($syncData as $request) {
            $method = $request->method;
            $batch[] = $this->guzzle->$method($request->ro->uri);
        }
        $this->body = array();
        $responses = $this->guzzle->batch($batch);
        foreach ($responses as $response) {
            list($code, $headers, $body) = $this->parseResponse($response);
            $this->body[] = $body;
        }
        return $this;
    }
}
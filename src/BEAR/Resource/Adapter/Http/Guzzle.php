<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @package BEAR.Resource
 */
namespace BEAR\Resource\Adapter\Http;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\Request;
use Guzzle\Service\Client as GuzzleClient;
use Guzzle\Common\Cache\DoctrineCacheAdapter;
use Guzzle\Http\Plugin\CachePlugin;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Ray\Di\Di\Scope;

/**
 * App resource (app:://self/path/to/resource)
 *
 * @package BEAR.Resource
 *
 * @Scope("singleton")
 */
class Guzzle implements ResourceObject, HttpClientInterface
{
    /**
     * Code
     *
     * @var int
     */
    public $code;

    /**
     * Headers
     *
     * @var array
     */
    public $headers;

    /**
     * Body
     *
     * @var mixed
     */
    public $body;

    /**
     * HTTP Response
     *
     * @var RequestInterface
     */
    private $response;

    /**
     * Http client
     *
     * @var \Guzzle\Http\Client
     */
    private $guzzle;

    /**
     * Constructor
     *
     * @param \Guzzle\Service\Client $guzzle
     */
    public function __construct(GuzzleClient $guzzle)
    {
        $this->guzzle = $guzzle;
        $cacheAdapter = (function_exists(
            'apc_cache_info'
        )) ? 'Doctrine\Common\Cache\ApcCache' : 'Doctrine\Common\Cache\ArrayCache';
        $adapter = new DoctrineCacheAdapter(new $cacheAdapter());
        $cache = new CachePlugin($adapter, true);
        $this->guzzle->getEventDispatcher()->addSubscriber($cache);
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClientInterface::onGet()
     */
    public function onGet()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->get()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClientInterface::onPost()
     */
    public function onPost()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->post()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClientInterface::onPut()
     */
    public function onPut()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->put()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClientInterface::onDelete()
     */
    public function onDelete()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->delete()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClientInterface::onHead()
     */
    public function onHead()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->head()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see BEAR\Resource\Adapter\Http.HttpClientInterface::onOptions()
     */
    public function onOptions()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->options()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * Parse HTTP response
     *
     * @param \Guzzle\Http\Message\Response $response
     *
     * @return array
     */
    protected function parseResponse(Response $response)
    {
        /* @var $response \Guzzle\Http\Message\Response */
        $code = $response->getStatusCode();
        $headers = $response->getHeaders()->getAll();
        $body = $response->getBody(true);
        if (strpos($headers['Content-Type'][0], 'xml') !== false && $body) {
            $body = new \SimpleXMLElement($body);
        } elseif (strpos($headers['Content-Type'][0], 'json') !== false) {
            $body = json_decode($body);
        }

        return [$code, $headers, $body];
    }

    /**
     * Continue Sync
     *
     * @param Request      $request
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
     * @param Request      $request
     * @param \ArrayObject $syncData
     *
     * @return \BEAR\Resource\Adapter\Http\Guzzle
     */
    public function onFinalSync(Request $request, \ArrayObject $syncData)
    {
        unset($request);
        $batch = [];
        foreach ($syncData as $request) {
            $method = $request->method;
            $batch[] = $this->guzzle->$method($request->ro->uri);
        }
        $this->body = [];
        $responses = $this->guzzle->send($batch);
        foreach ($responses as $response) {
            list(, , $body) = $this->parseResponse($response);
            $this->body[] = $body;
        }

        return $this;
    }
}

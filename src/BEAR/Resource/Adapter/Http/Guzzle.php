<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\Http;

use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Service\Client as GuzzleClient;
use Ray\Di\Di\Scope;

/**
 * Http resource
 *
 * @Scope("singleton")
 */
class Guzzle extends ResourceObject implements HttpClientInterface
{
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
     * @param GuzzleClient $guzzle
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
     * {@inheritDoc}
     */
    public function onGet()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->get()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onPost()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->post()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onPut()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->put()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onDelete()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->delete()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onHead()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->head()->send();
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
        $headers = $response->getHeaders()->toArray();
        $body = $response->getBody(true);
        $contentType = isset($headers['Content-Type']) ? $headers['Content-Type'] : $headers['content-type'];
        if (strpos($contentType[0], 'xml') !== false && $body) {
            $body = new \SimpleXMLElement($body);
        } elseif (strpos($contentType[0], 'json') !== false) {
            $body = json_decode($body);
        }

        return [$code, $headers, $body];
    }

    /**
     * {@inheritDoc}
     */
    public function onOptions()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->options()->send();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
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

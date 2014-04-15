<?php
/**
 * This file is part of the BEAR.Resource package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Resource\Adapter\Http;

use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Subscriber\History;

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
     * @param ClientInterface $guzzle
     */
    public function __construct(ClientInterface $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * {@inheritDoc}
     */
    public function onGet()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->get();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onPost()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->post();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onPut()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->put();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onDelete()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->delete();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function onHead()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->response = $this->guzzle->head();
        list($this->code, $this->headers, $this->body) = $this->parseResponse($this->response);

        return $this;
    }

    /**
     * Parse HTTP response
     *
     * @param ResponseInterface $response
     *
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        $code = $response->getStatusCode();
        $body = $response->getBody(true);
        $headersArray = $response->getHeaders();
        $headers = array_change_key_case($headersArray);
        $contentType = $headers['content-type'];
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
        $this->response = $this->guzzle->options();
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

        $history = new History;
        $this->guzzle->getEmitter()->attach($history);

        $requests = [];
        foreach ($syncData as $request) {
            $requests[] = $this->guzzle->createRequest(strtoupper($request->method), $request->ro->uri);
        }
        $this->body = [];
        $this->guzzle->sendAll($requests);
        $iterator = $history->getIterator();

        foreach ($iterator as $transaction) {
            $response = $transaction['response'];
            /** @var $response \GuzzleHttp\Message\Response */
            $this->body[] = $response->getBody();
        }

        return $this;
    }
}

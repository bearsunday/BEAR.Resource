<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function is_array;
use function strtoupper;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @method HttpResourceObject get(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject head(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject put(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject post(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject patch(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject delete(AbstractUri|string $uri, array $params = [])
 */
final class HttpResourceObject extends ResourceObject
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var ResponseInterface
     */
    private $response;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        unset($this->code, $this->headers, $this->body, $this->view);
    }

    public function __get(string $name)
    {
        if ($name === 'code') {
            return $this->response->getStatusCode();
        }
        if ($name === 'headers') {
            return $this->response->getHeaders();
        }
        if ($name === 'body') {
            return $this->response->toArray();
        }
        if ($name === 'view') {
            return $this->response->getContent();
        }

        throw new \InvalidArgumentException($name);
    }

    public function __set(string $name, $value) : void
    {
        throw new \InvalidArgumentException($name);
    }

    public function __isset(string $name) : bool
    {
        return isset($this->{$name});
    }

    public function __toString() : string
    {
        return $this->response->getContent();
    }

    public function request(AbstractRequest $request)
    {
        $uri = $request->resourceObject->uri;
        $method = strtoupper($uri->method);
        $options = ($method === 'GET') ? ['query' => $uri->query] : ['body' => $uri->query];
        $clientOptions = isset($uri->query['_options']) && is_array($uri->query['_options']) ? $uri->query['_options'] : [];
        $options += $clientOptions;
        $this->response = $this->client->request($method, (string) $uri, $options);

        return $this;
    }
}

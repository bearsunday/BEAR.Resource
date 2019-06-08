<?php

declare(strict_types=1);

namespace BEAR\Resource;

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

    public function __call(string $name, array $arguments = ['get', []])
    {
        $method = strtoupper($name);
        $params = isset($arguments[1]) ? $arguments[1] : [];
        $options = ($method === 'GET') ? ['query' => $params] : ['body' => $params];
        $this->response = $this->client->request(strtoupper($name), $arguments[0], $options);

        return $this;
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
        $this->{$name} = $value;
    }

    public function __isset(string $name) : bool
    {
        return isset($this->{$name});
    }

    public function __toString() : string
    {
        return $this->response->getContent();
    }
}

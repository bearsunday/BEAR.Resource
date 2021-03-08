<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BadFunctionCallException;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function is_array;
use function strtoupper;

/**
 * @method HttpResourceObject get(AbstractUri|string $uri, array<string, mixed> $params = [])
 * @method HttpResourceObject head(AbstractUri|string $uri, array<string, mixed> $params = [])
 * @method HttpResourceObject put(AbstractUri|string $uri, array<string, mixed> $params = [])
 * @method HttpResourceObject post(AbstractUri|string $uri, array<string, mixed> $params = [])
 * @method HttpResourceObject patch(AbstractUri|string $uri, array<string, mixed> $params = [])
 * @method HttpResourceObject delete(AbstractUri|string $uri, array<string, mixed> $params = [])
 * @property-read string        $code
 * @property-read array<string, string> $headers
 * @property-read array<string, string> $body
 * @property-read string        $view
 */
final class HttpResourceObject extends ResourceObject
{
    /** {@inheritdoc} */
    public $body;

    /** @var HttpClientInterface */
    private $client;

    /**
     * @var ResponseInterface
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $response;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        unset($this->code, $this->headers, $this->body, $this->view);
    }

    /**
     * @param 'code'|'headers'|'body'|'view'|string $name
     *
     * @return array<int|string, mixed>|int|string
     */
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

        throw new InvalidArgumentException($name);
    }

    /**
     * @param mixed $value
     */
    public function __set(string $name, $value): void
    {
        unset($value);

        throw new BadFunctionCallException($name);
    }

    public function __isset(string $name): bool
    {
        return isset($this->{$name});
    }

    public function __toString(): string
    {
        return $this->response->getContent();
    }

    public function request(AbstractRequest $request): self
    {
        $uri = $request->resourceObject->uri;
        $method = strtoupper($uri->method);
        $options = $method === 'GET' ? ['query' => $uri->query] : ['body' => $uri->query];
        $clientOptions = isset($uri->query['_options']) && is_array($uri->query['_options']) ? $uri->query['_options'] : [];
        $options += $clientOptions;
        $this->response = $this->client->request($method, (string) $uri, $options);

        return $this;
    }
}

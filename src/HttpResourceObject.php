<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BadFunctionCallException;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

use function count;
use function is_array;
use function strtoupper;
use function ucwords;

/**
 * @method HttpResourceObject get(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject head(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject put(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject post(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject patch(AbstractUri|string $uri, array $params = [])
 * @method HttpResourceObject delete(AbstractUri|string $uri, array $params = [])
 * @property-read string                $code
 * @property-read array<string, string> $headers
 * @property-read array<string, string> $body
 * @property-read string                $view
 */
final class HttpResourceObject extends ResourceObject implements InvokeRequestInterface
{
    /** {@inheritDoc} */
    public $body;

    /** @psalm-suppress PropertyNotSetInConstructor */
    private ResponseInterface $response;

    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
        unset($this->code, $this->headers, $this->body, $this->view);
    }

    /**
     * @param 'code'|'headers'|'body'|'view'|string $name
     *
     * @return array<int|string, mixed>|int|string
     */
    public function __get(string $name): array|int|string
    {
        if ($name === 'code') {
            return $this->response->getStatusCode();
        }

        if ($name === 'headers') {
            /** @var array<string, array<string>> $headers */
            $headers = $this->response->getHeaders();

            return $this->formatHeader($headers);
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
     * @param array<string, array<string>> $headers
     *
     * @return array<string, string|array<string>>
     */
    private function formatHeader(array $headers): array
    {
        $formated = [];
        foreach ($headers as $key => $header) {
            $ucFirstKey = ucwords($key);
            $formated[$ucFirstKey] = count($header) === 1 ? $header[0] : $header;
        }

        return $formated;
    }

    public function __set(string $name, mixed $value): void
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

    public function _invokeRequest(InvokerInterface $invoker, AbstractRequest $request): ResourceObject
    {
        unset($invoker);
        $uri = $request->resourceObject->uri;
        $method = strtoupper($uri->method);
        $options = $method === 'GET' ? ['query' => $uri->query] : ['body' => $uri->query];
        $clientOptions = isset($uri->query['_options']) && is_array($uri->query['_options']) ? $uri->query['_options'] : [];
        $options += $clientOptions;
        $this->response = $this->client->request($method, (string) $uri, $options);

        return $this;
    }
}

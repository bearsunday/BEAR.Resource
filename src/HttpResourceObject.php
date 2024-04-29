<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function is_array;
use function strtoupper;

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
    public function __construct(
        private HttpRequestCurl $httpRequest,
    ) {
    }

    public function _invokeRequest(InvokerInterface $invoker, AbstractRequest $request): ResourceObject
    {
        unset($invoker);

        return $this->request($request);
    }

    public function request(AbstractRequest $request): ResourceObject
    {
        $uri = $request->resourceObject->uri;
        $method = strtoupper($uri->method);
        $options = $method === 'GET' ? ['query' => $uri->query] : ['body' => $uri->query];
        $clientOptions = isset($uri->query['_options']) && is_array($uri->query['_options']) ? $uri->query['_options'] : [];
        $options += $clientOptions;

        /** @var array<string, string> $options */
        [
            'code' => $this->code,
            'headers' => $this->headers,
            'body' => $this->body,
            'view' => $this->view,
        ] =  $this->httpRequest->request($method, (string) $uri, $uri->query, $options);

        return $this;
    }

    public function __toString(): string
    {
        return $this->view;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Resource;

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
        private HttpRequestInterface $httpRequest,
    ) {
    }

    /** @SuppressWarnings(PHPMD.CamelCaseMethodName) */
    public function _invokeRequest(InvokerInterface $invoker, AbstractRequest $request): ResourceObject
    {
        unset($invoker);

        return $this->request($request);
    }

    public function request(AbstractRequest $request): ResourceObject
    {
        $uri = $request->resourceObject->uri;
        $method = strtoupper($uri->method);
        [
            'code' => $this->code,
            'headers' => $this->headers,
            'body' => $this->body,
            'view' => $this->view,
        ] =  $this->httpRequest->request($method, (string) $uri, $uri->query);

        return $this;
    }

    public function __toString(): string
    {
        return $this->view;
    }
}

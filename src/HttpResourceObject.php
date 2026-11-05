<?php

declare(strict_types=1);

namespace BEAR\Resource;

use Override;

use function strtoupper;

/**
 * @method HttpResourceObject get(AbstractUri|string $uri, Query $params = [])
 * @method HttpResourceObject head(AbstractUri|string $uri, Query $params = [])
 * @method HttpResourceObject put(AbstractUri|string $uri, Query $params = [])
 * @method HttpResourceObject post(AbstractUri|string $uri, Query $params = [])
 * @method HttpResourceObject patch(AbstractUri|string $uri, Query $params = [])
 * @method HttpResourceObject delete(AbstractUri|string $uri, Query $params = [])
 * @property-read int        $code
 * @property-read Headers    $headers
 * @property-read HttpBody   $body
 * @property-read string     $view
 * @psalm-import-type Query from Types
 * @psalm-import-type Headers from Types
 * @psalm-import-type HttpBody from Types
 */
final class HttpResourceObject extends ResourceObject implements InvokeRequestInterface
{
    public function __construct(
        private readonly HttpRequestInterface $httpRequest,
    ) {
    }

    /** @SuppressWarnings(PHPMD.CamelCaseMethodName) Underscore prefix indicates internal API method */
    #[Override]
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

    /** @psalm-capabilities read-props */
    public function __toString(): string
    {
        return $this->view;
    }
}

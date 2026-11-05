<?php

declare(strict_types=1);

namespace BEAR\Resource;

/**
 * Sends a HTTP request
 *
 * @psalm-import-type Query from Types
 * @psalm-import-type HttpHeaders from Types
 * @psalm-import-type HttpBody from Types
 * @psalm-import-type HttpResponse from Types
 */
interface HttpRequestInterface
{
    /**
     * Sends a HTTP request
     *
     * @param string $method The HTTP method (GET, POST, PUT, DELETE, etc.).
     * @param string $uri    The URL of the request.
     * @param Query  $query  An associative array of query parameters.
     *
     * @return HttpResponse An associative array containing the response information.
     *     - code: The HTTP response code.
     *     - headers: An array of response headers.
     *     - body: The parsed response body.
     *     - view: The raw response body.
     */
    public function request(string $method, string $uri, array $query): array;
}

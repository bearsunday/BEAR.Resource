<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\HttpRequestException;
use CurlHandle;
use JsonException;
use Override;

use function assert;
use function count;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function explode;
use function http_build_query;
use function is_string;
use function json_decode;
use function str_contains;
use function strtolower;
use function substr;
use function trim;

use const CURLINFO_CONTENT_TYPE;
use const CURLINFO_HEADER_SIZE;
use const CURLINFO_HTTP_CODE;
use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_URL;
use const JSON_THROW_ON_ERROR;

/**
 * Sends a HTTP request using cURL
 *
 * @psalm-import-type Query from Types
 * @psalm-import-type HttpHeaders from Types
 * @psalm-import-type HttpBody from Types
 * @psalm-import-type RequestOptions from Types
 */
final readonly class HttpRequestCurl implements HttpRequestInterface
{
    private const CONNECT_TIMEOUT_SECONDS = 5;
    private const REQUEST_TIMEOUT_SECONDS = 30;

    /** @psalm-pure */
    public function __construct(
        private HttpRequestHeaders $requestHeaders,
    ) {
    }

    /**
     * @inheritDoc
     * @psalm-taint-sink ssrf $uri
     */
    #[Override]
    public function request(string $method, string $uri, array $query): array
    {
        $body = http_build_query($query);
        $curl = $this->initializeCurl($method, $uri, $body);
        $response = curl_exec($curl);
        if (! is_string($response)) {
            throw new HttpRequestException(curl_error($curl));
        }

        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerString = substr($response, 0, $headerSize);
        $view = substr($response, $headerSize);
        $headers = $this->parseResponseHeaders($headerString);

        $body = $this->parseBody($curl, $view);

        return [
            'code' => $code,
            'headers' => $headers,
            'body' => $body,
            'view' => $view,
        ];
    }

    /** @psalm-taint-sink ssrf $uri */
    private function initializeCurl(string $method, string $uri, string $body): CurlHandle
    {
        $curl = curl_init();
        if ($curl === false) {
            throw new HttpRequestException('Failed to initialize cURL'); // @codeCoverageIgnore
        }

        assert($method !== '');
        assert($uri !== '');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT_SECONDS);
        curl_setopt($curl, CURLOPT_TIMEOUT, self::REQUEST_TIMEOUT_SECONDS);

        if ($this->requestHeaders->headers !== []) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->requestHeaders->headers);
        }

        if ($body !== '') {
            // Set the request body
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        return $curl;
    }

    /**
     * @return HttpHeaders
     *
     * @psalm-pure
     */
    private function parseResponseHeaders(string $responseHeaders): array
    {
        $responseHeadersArray = [];
        $headerLines = explode("\r\n", $responseHeaders);
        foreach ($headerLines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = $parts[0];

            $responseHeadersArray[$key] = trim($parts[1]);
        }

        return $responseHeadersArray;
    }

    /**
     * @return HttpBody
     *
     * @psalm-taint-source input
     *
     * @psalm-capabilities pure
     */
    private function parseBody(CurlHandle $curl, string $view): array
    {
        $responseBody = [];
        $contentType = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        if (str_contains(strtolower($contentType), 'application/json')) {
            if ($view === '') {
                return $responseBody;
            }

            try {
                return (array) json_decode($view, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                throw new HttpRequestException($e->getMessage(), Code::ERROR, $e);
            }
        }

        return $responseBody;
    }
}

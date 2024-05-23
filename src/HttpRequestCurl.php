<?php

declare(strict_types=1);

namespace BEAR\Resource;

use CurlHandle;

use function count;
use function curl_close;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function explode;
use function http_build_query;
use function json_decode;
use function strpos;
use function strtolower;
use function substr;
use function trim;

use const CURLINFO_CONTENT_TYPE;
use const CURLINFO_HEADER_SIZE;
use const CURLINFO_HTTP_CODE;
use const CURLOPT_CUSTOMREQUEST;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_URL;

/**
 * Sends a HTTP request using cURL
 *
 * @psalm-type RequestOptions = array<null>|array{"body?": string, "headers?": array<string, string>}
 * @psalm-type RequestHeaders = array<string, string>
 * @psalm-type Body = array<mixed>
 */
final class HttpRequestCurl implements HttpRequestInterface
{
    public function __construct(
        private HttpRequestHeaders $requestHeaders,
    ) {
    }

    /** @inheritdoc */
    public function request(string $method, string $uri, array $query): array
    {
        $body = http_build_query($query);
        $curl = $this->initializeCurl($method, $uri, $body);
        $response = (string) curl_exec($curl);
        $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerString = substr($response, 0, $headerSize);
        $view = substr($response, $headerSize);
        $headers = $this->parseResponseHeaders($headerString);
        curl_close($curl);

        $body = $this->parseBody($curl, $view);

        return [
            'code' => $code,
            'headers' => $headers,
            'body' => $body,
            'view' => $view,
        ];
    }

    private function initializeCurl(string $method, string $uri, string $body): CurlHandle
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $uri);

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

    /** @return array<string, string> */
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

    /** @return array<mixed> */
    private function parseBody(CurlHandle $curl, string $view): array
    {
        $responseBody = [];
        $contentType = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        if (strpos(strtolower($contentType), 'application/json') !== false) {
            return (array) json_decode($view, true);
        }

        return $responseBody;
    }
}

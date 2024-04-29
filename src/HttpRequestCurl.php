<?php

declare(strict_types=1);

namespace BEAR\Resource;

use CurlHandle;

use function assert;
use function count;
use function curl_close;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt;
use function explode;
use function http_build_query;
use function is_array;
use function json_decode;
use function json_encode;
use function parse_str;
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

// ... Other code is omitted for brevity

/**
 * Sends a HTTP request using cURL
 *
 * @psalm-type RequestOptions = array<null>|array{"body?": string, "headers?": array<string, string>}
 * @psalm-type RequestHeaders = array<string, string>
 * @psalm-type Body = array<mixed>
 */
final class HttpRequestCurl
{
    /**
     * Sends a HTTP request using cURL
     *
     * @param string         $method  The HTTP method (GET, POST, PUT, DELETE, etc.).
     * @param string         $uri     The URL of the request.
     * @param RequestOptions $options Additional options for the request.
     *                       - headers: An array of headers to be sent with the request.
     *                       - body: The request body.
     *
     * @return array{body: Body, code: int, headers: Headers, view: string}
     *      An associative array containing the response information.
     *     - code: The HTTP response code.
     *     - headers: An array of response headers.
     *     - body: The parsed response body.
     *     - view: The raw response body.
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        $curl = $this->initializeCurl($method, $uri, $options);
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

    /** @param RequestOptions $options $ */
    private function initializeCurl(string $method, string $uri, array $options): CurlHandle
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $uri);

        $requestHeaders = [];
        if (isset($options['headers'])) {
            $optionsHeaders = $options['headers'];
            assert(is_array($optionsHeaders));
            $requestHeaders = $this->headersFromOptions($optionsHeaders);
            if (! empty($requestHeaders)) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeaders);
            }
        }

        if (isset($options['body'])) {
            $optionBody = $options['body'];
            $contentType = $requestHeaders['content-type'] ?? $requestHeaders['Content-Type'] ?? 'application/x-www-form-urlencoded';
            $this->setCurlRequestPayload($contentType, $curl, $optionBody);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);

        return $curl;
    }

    /**
     * Extracts headers from the options array
     *
     * @param Headers $headers
     *
     * @return array<string, string>
     */
    private function headersFromOptions(array $headers): array
    {
        $requestHeaders = [];
        foreach ($headers as $header) {
            $parts = explode(':', $header, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $requestHeaders[$parts[0]] = trim($parts[1]);
        }

        return $requestHeaders;
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

        if (strpos(strtolower($contentType), 'application/x-www-form-urlencoded') !== false) {
            parse_str($view, $responseBody);
        }

        return $responseBody;
    }

    /** @param array<string, mixed>|string $optionBody */
    private function setCurlRequestPayload(string $contentType, CurlHandle $curl, string|array $optionBody): void
    {
        $strippedContentType = strtolower($contentType);
        $isJson = strpos($strippedContentType, 'application/json') !== false;
        $isUrlEncoded = ! $isJson && strpos($strippedContentType, 'application/x-www-form-urlencoded') !== false;
        $isNotDetermined = ! $isJson && ! $isUrlEncoded;
        if ($isJson || $isNotDetermined) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($optionBody));
        }

        if (! $isUrlEncoded) {
            return;
        }

        /** @var array<string, string> $optionBody */
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($optionBody));
    }
}

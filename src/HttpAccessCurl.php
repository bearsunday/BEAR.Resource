<?php

namespace BEAR\Resource;

use CURLFile;

final class HttpAccessCurl
{
    /**
     * Set the properties of a ResourceObject.
     *
     * @param ResourceObject              $ro      The ResourceObject to set the properties for.
     * @param string                      $method  The HTTP method to use for the request.
     * @param string                      $uri     The URL to send the request to.
     * @param array<string, string|array> $options Additional options for the request (optional).
     *
     * @return void
     */
    public function httpRequest(ResourceObject $ro, string $method, string $uri, array $options = []): void //@phpstan-ignore-line
    {
        $curl = curl_init();
        // Set Request Method
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        // Set Request URL
        curl_setopt($curl, CURLOPT_URL, $uri);
        $requestHeaders = [];
        if (isset($options['headers']) && is_array($options['headers'])) {
            foreach ($options['headers'] as $header) {
                assert(is_string($header));
                $parts = explode(':', $header, 2);
                if (count($parts) !== 2) {
                    continue;
                }

                $requestHeaders[$parts[0]] = trim($parts[1]);
            }
        }

        if (!empty($requestHeaders)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array_map(static function ($key, $value) {
                return "$key: $value";
            }, array_keys($requestHeaders), $requestHeaders));
        }

        if (isset($options['body'])) {
            $optionBody = $options['body'];
            $contentType = $requestHeaders['Content-Type'] ?? 'application/x-www-form-urlencoded';
            $strippedContentType = strtolower($contentType);
            $isJson = strpos($strippedContentType, 'application/json') !== false;
            $isUrlEncoded = !$isJson && strpos($strippedContentType, 'application/x-www-form-urlencoded') !== false;
            $isMultipart = !$isJson && !$isUrlEncoded && strpos($strippedContentType, 'multipart/form-data') !== false;
            $isNotDetermined = !$isJson && !$isUrlEncoded && !$isMultipart;
            if ($isJson || $isNotDetermined) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($optionBody));
            }

            if ($isUrlEncoded) {
                /** @var array<string, string> $optionBody */
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($optionBody));
            }

            if ($isMultipart) {
                $multipartBody = [];
                $body = $options['body'];
                assert(is_array($body));
                /** @psalm-suppress MixedAssignment */
                foreach ($body as $key => $value) {
                    $multipartBody[$key] = $value instanceof CURLFile ? $value : (string)$value;
                }

                curl_setopt($curl, CURLOPT_POSTFIELDS, $multipartBody);
            }
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $response = (string)curl_exec($curl);
        $responseCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = (int)curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($response, 0, $headerSize);
        $view = substr($response, $headerSize);
        $responseHeadersArray = [];
        $headerLines = explode("\r\n", $responseHeaders);
        foreach ($headerLines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $responseHeadersArray[$parts[0]] = trim($parts[1]);
        }

        $responseBody = [];
        $contentType = (string)curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        if (strpos(strtolower($contentType), 'application/json') !== false) {
            /** @var array<string, mixed> $responseBody */
            $responseBody = json_decode($view, true);
        } elseif (strpos(strtolower($contentType), 'application/x-www-form-urlencoded') !== false) {
            parse_str($view, $responseBody);
        }

        curl_close($curl);
        $ro->code = $responseCode;
        $ro->headers = $responseHeadersArray;
        $ro->body = $responseBody;
        $ro->view = $view;
    }
}

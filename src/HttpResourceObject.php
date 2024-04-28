<?php

declare(strict_types=1);

namespace BEAR\Resource;

use CURLFile;

use function array_keys;
use function array_map;
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
use function is_string;
use function json_decode;
use function json_encode;
use function parse_str;
use function strpos;
use function strtolower;
use function strtoupper;
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
        $this->httpRequest($this, $method, (string) $uri, $options);

        return $this;
    }

    public function __toString(): string
    {
        return $this->view;
    }

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
    private function httpRequest(ResourceObject $ro, string $method, string $uri, array $options = []): void //@phpstan-ignore-line
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

        if (! empty($requestHeaders)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array_map(static function ($key, $value) {
                return "$key: $value";
            }, array_keys($requestHeaders), $requestHeaders));
        }

        if (isset($options['body'])) {
            $optionBody = $options['body'];
            $contentType = $requestHeaders['Content-Type'] ?? 'application/x-www-form-urlencoded';
            $strippedContentType = strtolower($contentType);
            $isJson = strpos($strippedContentType, 'application/json') !== false;
            $isUrlEncoded = ! $isJson && strpos($strippedContentType, 'application/x-www-form-urlencoded') !== false;
            $isMultipart = ! $isJson && ! $isUrlEncoded && strpos($strippedContentType, 'multipart/form-data') !== false;
            $isNotDetermined = ! $isJson && ! $isUrlEncoded && ! $isMultipart;
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
                    $multipartBody[$key] = $value instanceof CURLFile ? $value : (string) $value;
                }

                curl_setopt($curl, CURLOPT_POSTFIELDS, $multipartBody);
            }
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        $response = (string) curl_exec($curl);
        $responseCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);
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
        $contentType = (string) curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
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

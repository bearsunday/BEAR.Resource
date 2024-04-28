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
    private HttpAccessCurl $httpAccessCurl;

    public function __construct()
    {
        $this->httpAccessCurl = new HttpAccessCurl($this);
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
        $this->httpAccessCurl->httpRequest($ro, $method, $uri, $options);
    }
}

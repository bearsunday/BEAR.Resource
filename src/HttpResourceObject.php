<?php

declare(strict_types=1);

namespace BEAR\Resource;

use function array_keys;
use function array_map;
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
use function stripos;
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

    public function request(AbstractRequest $request): self
    {
        $uri = $request->resourceObject->uri;
        $method = strtoupper($uri->method);
        $options = $method === 'GET' ? ['query' => $uri->query] : ['body' => $uri->query];
        $clientOptions = isset($uri->query['_options']) && is_array($uri->query['_options']) ? $uri->query['_options'] : [];
        $options += $clientOptions;
        $curlResponse = $this->curlClient($method, (string) $uri, $options);
        $this->code = $curlResponse['code'];
        $this->headers = $curlResponse['headers'];
        $this->body = $curlResponse['body'];

        return $this;
    }

    public function __toString(): string
    {
        return $this->view;
    }

    function curlClient($method, $uri, $options = [])
    {
        $curl = curl_init();

        // リクエストメソッドを設定
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // リクエストURLを設定
        curl_setopt($curl, CURLOPT_URL, $uri);

        // リクエストヘッダーを設定
        $requestHeaders = [];
        if (isset($options['headers'])) {
            foreach ($options['headers'] as $header) {
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

        // リクエストボディを設定
        if (isset($options['body'])) {
            $contentType = $requestHeaders['Content-Type'] ?? 'application/json';
            if (stripos($contentType, 'application/json') !== false) {
                // JSONの場合はエンコードしてボディを設定
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['body']));
            } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
                // フォームの場合はhttp_build_queryでエンコードしてボディを設定
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($options['body']));
            } elseif (stripos($contentType, 'multipart/form-data') !== false) {
                // マルチパートの場合はCURLFile objectを使用してボディを設定
                $multipartBody = [];
                foreach ($options['body'] as $key => $value) {
                    if ($value instanceof CURLFile) {
                        $multipartBody[$key] = $value;
                    } else {
                        $multipartBody[$key] = (string) $value;
                    }
                }

                curl_setopt($curl, CURLOPT_POSTFIELDS, $multipartBody);
            } else {
                // Content-Typeが指定されていない場合は"application/json"としてエンコード
                $requestHeaders['Content-Type'] = 'application/json';
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['body']));
            }
        }

        // レスポンスボディを文字列として取得するように設定
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // レスポンスヘッダーを含めるように設定
        curl_setopt($curl, CURLOPT_HEADER, true);

        // リクエストを実行
        $response = curl_exec($curl);

        // レスポンスコードを取得
        $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        // レスポンスヘッダーとボディを分割
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);

        // レスポンスヘッダーを連想配列に変換
        $responseHeadersArray = [];
        $headerLines = explode("\r\n", $responseHeaders);
        foreach ($headerLines as $line) {
            $parts = explode(':', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $responseHeadersArray[$parts[0]] = trim($parts[1]);
        }

        // レスポンスのContent-Typeを取得
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        // レスポンスボディをパース
        if (stripos($contentType, 'application/json') !== false) {
            // JSONの場合はデコード
            $responseBody = json_decode($responseBody, true);
        } elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false) {
            // フォームの場合はパース
            parse_str($responseBody, $responseBody);
        }

        // cURLセッションを閉じる
        curl_close($curl);

        // レスポンスコード、ヘッダー、ボディを返す
        return [
            'code' => $responseCode,
            'headers' => $responseHeadersArray,
            'body' => $responseBody,
        ];
    }
}

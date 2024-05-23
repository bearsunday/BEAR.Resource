<?php

declare(strict_types=1);

$body = (string) file_get_contents('php://input');
parse_str($body, $form);
$headers = getallheaders();
ksort($headers);

if (isset($headers['Content-Type']) && $headers['Content-Type'] === 'text/html') {
    http_response_code(200);
    echo '<html></html>';
    exit(0);
}

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
$requestHeaders = [];
foreach ($_SERVER as $key => $value) {
    if (strncmp($key, 'HTTP_', 5) !== 0) {
        continue;
    }

    $requestHeaders[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
}

echo json_encode(
    [
        'args' => $_GET,
        'method' => $_SERVER['REQUEST_METHOD'],
        'headers' => $headers,
        'url' => empty($_SERVER['HTTPS']) ? 'http://' : 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ,
        'form' => $form,
        'body' => $body,
        'request_headers' => $requestHeaders,
        'server' => $_SERVER,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
);

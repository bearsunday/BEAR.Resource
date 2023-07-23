<?php

declare(strict_types=1);

$body = (string) file_get_contents('php://input');
parse_str($body, $form);
$headers = getallheaders();
ksort($headers);

http_response_code(200);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(
    [
        'args' => $_GET,
        'method' => $_SERVER['REQUEST_METHOD'],
        'headers' => $headers,
        'url' => empty($_SERVER['HTTPS']) ? 'http://' : 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ,
        'form' => $form,
        'body' => $body,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
);

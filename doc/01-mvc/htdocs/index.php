<?php
namespace helloWorld;

// $reource, $page $di are provided by bootstrap.
include dirname(__DIR__) . '/script/bootstrap.php';
// request page, get response.
$response = $resource->get->object($page)->eager->request();
// render
include dirname(__DIR__) . '/View/Hello.php';
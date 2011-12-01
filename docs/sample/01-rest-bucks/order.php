<?php
/**
 * RESTbucks simple example
 *
 * @package BEAR.Resource
 * @see http://www.infoq.com/articles/webber-rest-workflow
 */

$base = dirname(dirname(dirname(__DIR__)));
require $base . '/src.php';
require $base . '/vendors/Ray.Di/src.php';
require $base . '/tests/bootstrap.php';
$resource = require $base . '/scripts/instance.php';

// order latte.
$order = array('drink' => 'latte');
$order = $resource->post->uri('app://self/RestBucks/Order')->withQuery($order)->eager->request();

// get response and hyper link.
$paymentUri = $order->headers['rel=payment'];
$payment = array(
	'credit_card_number' => '123456789',
	'expires' => '07/07',
	'name' => 'John Citizen',
	'amount' => '4.00'
);

// requet payment using hyper link.
$response = $resource->put->uri($paymentUri)->addQuery($payment)->eager->request();

// payment done, enjoy coffee !
$expected = 201;
$code = new \BEAR\Resource\Code;
echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
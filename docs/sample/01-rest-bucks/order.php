<?php
/**
 * RESTbucks simple example
 *
 * @package BEAR.Resource
 * @see http://www.infoq.com/articles/webber-rest-workflow
 */

use BEAR\Resource\SchemeCollection;
use BEAR\Resource\Adapter\App;
use BEAR\Resource\Code;

// loader
require __DIR__ . '/src.php';

// build resource client
$resource = require $base . '/scripts/instance.php';
$injector = require $base . '/scripts/injector.php';
$scheme = new SchemeCollection;
$scheme->scheme('app')->host('self')->toAdapter(new App($injector, 'restbucks', 'Resource\App'));
$resource->setSchemeCollection($scheme);

// order latte.
$orderDrink = ['drink' => 'latte'];
$order = $resource->post->uri('app://self/Order')->withQuery($orderDrink)->eager->request();

// get response and hyper link.
$paymentUri = $order->links['payment'];
$payment = [
	'credit_card_number' => '123456789',
	'expires' => '07/07',
	'name' => 'John Citizen',
	'amount' => '4.00'
];

// requet payment using hyper link.
$response = $resource->put->uri($paymentUri)->addQuery($payment)->eager->request();

// payment done, enjoy coffee !
$code = new Code;
echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
echo 'Oreter: ' . (($response->code === 201) ? 'Success' : 'Failure'). PHP_EOL;
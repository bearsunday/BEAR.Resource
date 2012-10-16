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
use BEAR\Resource\A;
use Guzzle\Parser\UriTemplate\UriTemplate;

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

// get payment uri with hyperlink.
$a = new A(new UriTemplate);
$paymentUri = $a->href('payment', $order);
$payment = [
	'credit_card_number' => '123456789',
	'expires' => '07/07',
	'name' => 'John Citizen',
	'amount' => '4.00'
];

// request payment
$response = $resource->put->uri($paymentUri)->addQuery($payment)->eager->request();

// payment done, enjoy coffee !
$code = new Code;
echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
echo 'Order: ' . (($response->code === 201) ? 'Success' : 'Failure'). PHP_EOL;
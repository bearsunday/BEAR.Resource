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
use Ray\Di\Injector;

// load
chdir(dirname(dirname(dirname(__DIR__))));

require __DIR__ . '/src.php';
$resource = require 'scripts/instance.php';

$scheme = (new SchemeCollection)
          ->scheme('app')
          ->host('self')
          ->toAdapter(new App(Injector::create(), 'Restbucks', 'Resource\App'));
$resource->setSchemeCollection($scheme);

// order latte.
$order = $resource
    ->post
    ->uri('app://self/order')
    ->withQuery(['drink' => 'latte'])
    ->eager
    ->request();

$payment = [
    'credit_card_number' => '123456789',
    'expires' => '07/07',
    'name' => 'John Citizen',
    'amount' => '4.00'
];

$response = $resource->href('payment', $payment);

// payment done, enjoy coffee !
$code = new Code;
echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
echo 'Order: ' . (($response->code === 201) ? 'Success' : 'Failure'). PHP_EOL;

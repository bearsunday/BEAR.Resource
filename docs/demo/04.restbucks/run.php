<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Resource\Code;

main: {
    $resource = require __DIR__ . '/scripts/instance.php';

    // order latte
    $order = $resource
        ->post
        ->uri('app://self/order')
        ->withQuery(['drink' => 'latte'])
        ->eager
        ->request();

    $payment = [
        'credit_card_number' => '123456789',
        'expires' => '07/07',
        'name' => 'Koriym',
        'amount' => '4.00'
    ];

    // then use hyper link to pay
    $response = $resource->href('payment', $payment);
}

output: {
    // payment done, enjoy coffee !
    $code = new Code;
    echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
    echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
    echo 'Order: ' . (($response->code === 201) ? 'Success' : 'Failure') . PHP_EOL;
}

//201: Created
//Location: app://self/Order/?id=2033905881
//Order: Success

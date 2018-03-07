<?php

declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\Demo\Resource\App;
/*
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
use BEAR\Resource\Annotation\Link;
use BEAR\Resource\Code;
use BEAR\Resource\Module\HalModule;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Ray\Di\Injector;

require dirname(__DIR__) . '/vendor/autoload.php';

class Menu extends ResourceObject
{
    private $menu = [];

    /**
     * @param resource $resource
     */
    public function __construct()
    {
        $this->menu = ['coffee' => 300, 'latte' => 400];
    }

    /**
     * @Link(rel="order", href="app://self/Order?drink={drink}")
     */
    public function onGet($drink = null) : ResourceObject
    {
        if ($drink === null) {
            $this->body = $this->menu;

            return $this;
        }
        $this->body = [
            'drink' => $drink,
            'price' => $this->menu[$drink]
        ];

        return $this;
    }
}

class Order extends ResourceObject
{
    private $orders = [];

    public function onGet($id) : ResourceObject
    {
        $this->body = $this->orders[$id];

        return $this;
    }

    /**
     * @Link(rel="payment", href="app://self/payment{?order_id,credit_card_number,expires,name,amount}", method="put")
     */
    public function onPost($drink) : ResourceObject
    {
        // data store here
        //   .. and get order id.
        $orderId = mt_rand();
        $this->body = [
            'drink' => $drink,
            'order_id' => $orderId
        ];
        $this->code = 201; // created
        $this->headers['Location'] = "/order/?id=$orderId"; // hyper link

        return $this;
    }
}

class Payment extends ResourceObject
{
    public function onPut(
        string $order_id,
        string $credit_card_number,
        string $expires,
        string $name,
        string $amount
    ) : ResourceObject {
        $this->code = 201;
        $this->headers['Location'] = "app://self/Order/?id={$order_id}";

        return $this;
    }
}

/* @var ResourceInterface $resource */
$resource = (new Injector(new HalModule(new ResourceModule('MyVendor\Demo')), __DIR__ . '/tmp'))->getInstance(ResourceInterface::class);
$resource->post->uri('app://self/order')(['drink' => 'latte']);
$payment = [
    'credit_card_number' => '123456789',
    'expires' => '07/07',
    'name' => 'Koriym',
    'amount' => '4.00'
];

// then use hyper link to pay
/* @var Order $ro */
$ro = $resource->href('payment', $payment);

// payment done, enjoy coffee !
$code = new Code;
echo "$ro->code: " . $code->statusText[$ro->code] . PHP_EOL;
echo 'Location: ' . $ro->headers['Location'] . PHP_EOL;
echo 'Order: ' . (($ro->code === 201) ? 'Success' : 'Failure') . PHP_EOL;

//201: Created
//Location: app://self/Order/?id=2033905881
//Order: Success

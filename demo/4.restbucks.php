<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace MyVendor\Sandbox\Resource\App;
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
    public function onGet($drink = null)
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

    public function onGet($id)
    {
        return $this->orders[$id];
    }

    /**
     * @Link(rel="payment", href="app://self/payment{?order_id,credit_card_number,expires,name,amount}", method="put")
     */
    public function onPost($drink)
    {
        // data store here
        //   .. and get order id.
        $orderId = mt_rand();
        $this['drink'] = $drink;
        $this['order_id'] = $orderId;

        // created
        $this->code = 201;
        $this->headers['Location'] = "app://self/Order/?id=$orderId";

        return $this;
    }
}

class Payment extends ResourceObject
{
    public function onPut($order_id, $credit_card_number, $expires, $name, $amount)
    {
        // payment transaction here..
        $this->code = 201;
        $this->headers['Location'] = "app://self/Order/?id={$order_id}";

        return $this;
    }
}

/** @var ResourceInterface $resource */
$resource = (new Injector(new HalModule(new ResourceModule('MyVendor\Sandbox'))))->getInstance(ResourceInterface::class);
$order = $resource->post->uri('app://self/order')(['drink' => 'latte']);
$payment = [
    'credit_card_number' => '123456789',
    'expires' => '07/07',
    'name' => 'Koriym',
    'amount' => '4.00'
];

// then use hyper link to pay
$response = $resource->href('payment', $payment);

// payment done, enjoy coffee !
$code = new Code;
echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
echo 'Order: ' . (($response->code === 201) ? 'Success' : 'Failure') . PHP_EOL;

//201: Created
//Location: app://self/Order/?id=2033905881
//Order: Success

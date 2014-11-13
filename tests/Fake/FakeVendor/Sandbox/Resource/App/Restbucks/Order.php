<?php

namespace FakeVendor\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use Ray\Di\Di\Scope;

/**
 * Order
 *
 * @Scope("Singleton")
 */
class Order extends ResourceObject
{
    private $orders = [];

    public function __construct()
    {
    }

    public function onGet($id)
    {
        return $this->orders[$id];
    }

    /**
     * Post
     *
     * @link(link="payment" rel="app://self/restbucks/payment/?order_id={orderId}" method="put")
     */
    public function onPost($drink)
    {
        // data store here
        //   .. and get order id.
        $orderId = 1234;
        $this->orders[$orderId] = $drink;

        // created
        $this->code = 201;
        $this->headers['Location'] = "app://self/restbucks/order/?id=$orderId";
        $this->links['payment'] = new Uri('app://self/restbucks//payment', ['order_id' => $orderId]);

        return $this;
    }
}

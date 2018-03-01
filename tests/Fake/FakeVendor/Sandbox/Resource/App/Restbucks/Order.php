<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\ResourceObject;
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

        return $this;
    }
}

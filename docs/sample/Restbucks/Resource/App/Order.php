<?php

namespace Restbucks\Resource\App;

use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Scope;

/**
 * Order
 */
class Order extends ResourceObject
{
    private $orders = [];

    public function onGet($id)
    {
        return $this->orders[$id];
    }

    public function onPost($drink)
    {
        // data store here
        //   .. and get order id.
        $orderId = rand();
        $this['drink'] = $drink;
        $this['order_id'] = $orderId;

        // created
        $this->code = 201;
        $this->headers['Location'] = "app://self/Order/?id=$orderId";
        $this->links['payment'] = ['href' => 'app://self/Payment{?order_id}', 'templated' => true];

        return $this;
    }
}

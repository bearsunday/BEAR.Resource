<?php

namespace restbucks\Resource\App;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource,
    BEAR\Resource\Uri;
use Ray\Di\Di\Scope;

/**
 * Order
 *
 * @Scope("singleton")
 */
class Order extends AbstractObject
{

    private $orders = array();

    public function onGet($id)
    {
        return $this->orders[$id];
    }

    /**
     * Post
     *
     * @param string $drink
     */
    public function onPost($drink)
    {
        // data store here
        //   .. and get order id.
        $orderId = 1234;
        $this->orders[$orderId] = $drink;

        // created
        $this->code = 201;
        $this->headers['Location'] = "app://self/Order/?id=$orderId";
        $this->links['payment'] = new Uri('app://self/Payment', array('order_id' => $orderId));

        return $this;
    }
}

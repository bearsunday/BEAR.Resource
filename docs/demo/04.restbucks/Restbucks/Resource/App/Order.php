<?php
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Restbucks\Resource\App;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;

class Order extends ResourceObject
{
    private $orders = [];

    public function onGet($id)
    {
        return $this->orders[$id];
    }

    /**
     * @param $drink
     *
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
        $this->links['payment'] = ['href' => 'app://self/Payment{?order_id}', 'templated' => true];

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace FakeVendor\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\Annotation\Link;
use BEAR\Resource\ResourceObject;
use Ray\Di\Di\Scope;

/**
 * Order
 *
 * @Scope("Singleton")
 */
class Order extends ResourceObject
{
    private array $orders = [];

    public function __construct()
    {
    }

    public function onGet(int $id)
    {
        return $this->orders[$id];
    }

    /**
     * @link(rel="payment", href="app://self/restbucks/payment/?order_id={orderId}", method="put")
     */
    #[Link(rel: "payment", href: "app://self/restbucks/payment/?order_id={orderId}", method: "put")]
    public function onPost(string $drink)
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

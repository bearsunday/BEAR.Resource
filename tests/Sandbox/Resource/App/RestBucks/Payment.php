<?php

namespace Sandbox\Resource\App\RestBucks;

use BEAR\Resource\ResourceObject;

class Payment extends ResourceObject
{
    public function __construct()
    {
    }

    public function onPut($order_id, $credit_card_number, $expires, $name, $amount)
    {
        // payment transaction here..
        $this->code = 201;
        $this->headers['Location'] = "app://self/RestBucks/Order/?id=$order_id";

        return $this;
    }
}

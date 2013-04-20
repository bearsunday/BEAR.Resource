<?php

namespace testworld\ResourceObject\RestBucks;

use BEAR\Resource\AbstractObject;

class Payment extends AbstractObject
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

<?php

namespace testworld\ResourceObject\RestBucks;

use BEAR\Resource\ObjectInterface as ResourceObject;
use BEAR\Resource\AbstractObject;
use BEAR\Resource\Resource;

class Payment extends AbstractObject
{
    /**
     * @param Resource $resource
     */
    public function __construct()
    {
    }

    /**
     * @param id
     *
     * @return array
     */
    public function onPut($order_id, $credit_card_number, $expires, $name, $amount)
    {
        // payment transaction here..
        $this->code = 201;
        $this->headers['Location'] = "app://self/RestBucks/Order/?id=$order_id";

        return $this;
    }
}

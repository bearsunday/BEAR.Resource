<?php

namespace restbucks\Resource\App;

use BEAR\Resource\Object as ResourceObject,
    BEAR\Resource\AbstractObject,
    BEAR\Resource\Resource;

class Payment extends AbstractObject
{
    /**
     * @param id
     *
     * @return array
     */
    public function onPut($order_id, $credit_card_number, $expires, $name, $amount)
    {
        // payment transaction here..
        $this->code = 201;
        $this->headers['Location'] = "app://self/Order/?id={$order_id}";

        return $this;
    }
}

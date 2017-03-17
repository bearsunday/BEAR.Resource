<?php
/**
 * This file is part of the BEAR.Sunday package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace Restbucks\Resource\App;

use BEAR\Resource\ResourceObject;

class Payment extends ResourceObject
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

<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Resource package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace FakeVendor\Sandbox\Resource\App\Restbucks;

use BEAR\Resource\ResourceObject;

class Payment extends ResourceObject
{
    public function onPut($order_id, $credit_card_number, $expires, $name, $amount)
    {
        // payment transaction here..
        $this->code = 201;
        $this->headers['Location'] = "app://self/restbucks/order/?id=$order_id";
        unset($order_id, $credit_card_number, $expires, $name, $amount);

        return $this;
    }
}

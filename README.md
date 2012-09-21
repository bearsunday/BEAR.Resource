BEAR.Resource, a RESTful service layer framework.
=================================================

[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Resource.png)](http://travis-ci.org/koriym/BEAR.git@github.com:koriym/BEAR.Resource.git)

 * Service Layer - _Defines an application's boundary with a layer of services that establishes a set of available operations and coordinates the application's response in each operation. (Martin Fowler - PoEAA)_
 * REST Web Services Characteristics - _Client-Server, Stateless, Cache, Uniform interface, Named resources, Interconnected resource representations, and Layered components._

BEAR.Resource is a combination of both technology.

Requiement
-------------

 * PHP 5.4+

RESTBucks sample
================

### What's RESTBucks ?
See this article.[How to GET a Cup of Coffee](http://www.infoq.com/articles/webber-rest-workflow)

Run Sample
----------
    $ php docs/sample/01-rest-bucks/order.php 
    201: Created
    Location: app://self/RestBucks/Order/?id=1234


Client Code
-----------
```php
<?php
/**
 * RESTbucks simple example
 *
 * @see http://www.infoq.com/articles/webber-rest-workflow
 */

// order latte.
// order latte.
$orderDrink = ['drink' => 'latte'];
$order = $resource->post->uri('app://self/Order')->withQuery($orderDrink)->eager->request();

// get response and hyper link.
$paymentUri = $order->links['payment'];
$payment = [
	'credit_card_number' => '123456789',
	'expires' => '07/07',
	'name' => 'John Citizen',
	'amount' => '4.00'
];

// requet payment using hyper link.
$response = $resource->put->uri($paymentUri)->addQuery($payment)->eager->request();
echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
echo 'Oreter: ' . (($response->code === 201) ? 'Success' : 'Failure'). PHP_EOL;
```

```php
201: Created
Location: app://self/Order/?id=1234
Oreter: Success
```

Service Code
------------

### Order
```php
<?php
class Order extends AbstractObject
{
    private $orders = [];

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
```

### Payment

```php
<?php
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
```		
		

Testing BEAR.Resource
===============

Here's how to install Ray.Aop from source to run the unit tests and sample:

```
$ git clone git://github.com/koriym/BEAR.Resource.git
$ (wget http://getcomposer.org/composer.phar)
$ php composer.phar update
$ phpunit
```

A Resource Oriented Framework
============
__BEAR.Sunday__ is a resource oriented framework using BEAR.Resource as well as Gooogle Guice clone DI/AOP system [Ray](https://github.com/koriym/Ray.Di).
See more at [BEAR.Sunday GitHub](https://github.com/koriym/BEAR.Sunday).

Installation
============

### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add Ray.Aop with it.

	{
		"require": {
			"bear/resource": ">=0.1"
		}
	}

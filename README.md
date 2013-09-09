BEAR.Resource, a RESTful object / service layer framework.
=================================================

[![Latest Stable Version](https://poser.pugx.org/bear/resource/v/stable.png)](https://packagist.org/packages/bear/resource)
[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Resource.png)](http://travis-ci.org/koriym/BEAR.git@github.com:koriym/BEAR.Resource.git)

 * Service Layer - _Defines an application's boundary with a layer of services that establishes a set of available operations and coordinates the application's response in each operation. (Martin Fowler - PoEAA)_
 * REST Web Services Characteristics - _Client-Server, Stateless, Cache, Uniform interface, Named resources, Interconnected resource representations, and Layered components._

BEAR.Resource is a combination of both technology.

Requirement
-------------

 * PHP 5.4+

RESTBucks sample
================

### What's RESTBucks ?
See this article.[How to GET a Cup of Coffee](http://www.infoq.com/articles/webber-rest-workflow)

Run hyper link application sample
----------
    $ php docs/sample/01-rest-bucks/order.php

    201: Created
    Location: app://self/RestBucks/Order/?id=1184049611


Client Code
-----------
```php
<?php
...

// order latte.
$orderDrink = ['drink' => 'latte'];
$order = $resource->post->uri('app://self/Order')->withQuery($orderDrink)->eager->request();

// get payment uri with hyperlink.
$a = new A(new UriTemplate);
$paymentUri = $a->href('payment', $order);
$payment = [
	'credit_card_number' => '123456789',
	'expires' => '07/07',
	'name' => 'John Citizen',
	'amount' => '4.00'
];

// request payment
$response = $resource->put->uri($paymentUri)->addQuery($payment)->eager->request();

// payment done, enjoy coffee !
```

```php
201: Created
Location: app://self/Order/?id=1184049611
Order: Success
```

Service Code
------------

### Order
```php
<?php
class Order extends ResourceObject
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
        $orderId = rand();
        $this['drink'] = $drink;
        $this['order_id'] = $orderId;

        // created
        $this->code = 201;
        $this->headers['Location'] = "app://self/Order/?id=$orderId";
        $this->links['payment'] = ['href' => 'app://self/Payment{?order_id}', 'templated' => true];

        return $this;
    }
}
```

### Payment

```php
<?php
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

Installation
============

### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add Ray.Aop with it.

	{
		"require": {
			"bear/resource": ">=0.1"
		}
	}

A Resource Oriented Framework
============
__BEAR.Sunday__ is a resource oriented framework using BEAR.Resource as well as Gooogle Guice clone DI/AOP system [Ray](https://github.com/koriym/Ray.Di).
See more at [BEAR.Sunday GitHub](https://github.com/koriym/BEAR.Sunday).

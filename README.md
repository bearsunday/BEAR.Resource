BEAR.Resource, a RESTful service layer framework.
=================================================

[![Build Status](https://secure.travis-ci.org/koriym/BEAR.Resource.png)](http://travis-ci.org/koriym/BEAR.git@github.com:koriym/BEAR.Resource.git)

 * Service Layer - _Defines an application's boundary with a layer of services that establishes a set of available operations and coordinates the application's response in each operation. (Martin Fowler - PoEAA)_
 * REST Web Services Characteristics - _Client-Server, Stateless, Cache, Uniform interface, Named resources, Interconnected resource representations, Layered components_

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
```php
<?php
    $ php docs/sample/01-rest-bucks/order.php 
	201: Created
	Location: app://self/RestBucks/Order/?id=1234
```

Client Code
-----------
```php
<?php
	<?php
	/**
	 * RESTbucks simple example
	 *
	 * @see http://www.infoq.com/articles/webber-rest-workflow
	 */

	// order latte.
	$order = array('drink' => 'latte');
	$order = $resource->post->uri('app://self/RestBucks/Order')->withQuery($order)->eager->request();

	// get response and hyper link.
	$paymentUri = $order->headers['rel=payment'];
	$payment = array(
		'credit_card_number' => '123456789',
		'expires' => '07/07',
		'name' => 'John Citizen',
		'amount' => '4.00'
	);

	// requet payment using hyper link.
	$response = $resource->put->uri($paymentUri)->addQuery($payment)->eager->request();

	// payment done, enjoy coffee !
	$expected = 201;
	$code = new \BEAR\Resource\Code;
	echo "$response->code: " . $code->statusText[$response->code] . PHP_EOL;
	echo 'Location: ' . $response->headers['Location'] . PHP_EOL;
```

### Service Code

Order
```php
<?php

	class Order
	{
		public function onPost($drink)
		{
		    // data store here
		    //   .. and get order id.
		    $orderId = 1234;
		    $this->orders[$orderId] = $drink;
		    // created
		    $this->code = 201;
		    $this->headers['Location'] = "app://self/RestBucks/Order/?id=$orderId";
		    $this->headers['rel=payment'] = new Uri('app://self/RestBucks/Payment', array('order_id' => $orderId));
		    return $this;
		}
```

Payment

```php
<?php

	class Payment
	{
		public function onPut($order_id, $credit_card_number, $expires, $name, $amount)
		{
		   // payment transaction here..
		   $this->code = 201;
		   $this->headers['Location'] = "app://self/RestBucks/Order/?id=$order_id";
		   return $this;
		}
		
		

Testing Ray.Aop
===============

Here's how to install Ray.Aop from source to run the unit tests and sample:

```
$ git clone git://github.com/koriym/BEAR.Resource.git
$ (wget http://getcomposer.org/composer.phar)
$ php composer.phar install
$ phpunit
```

Installation
============

### Install with Composer
If you're using [Composer](https://github.com/composer/composer) to manage dependencies, you can add Ray.Aop with it.

	{
		"require": {
			"Ray/Aop": ">=0.1"
		}
	}

## REST
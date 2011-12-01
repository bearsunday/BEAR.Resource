BEAR.Resource, a REST framework for PHP.
=============================

## Install
    $ git clone git://github.com/koriym/BEAR.Resource.git
    $ cd BEAR.Resource
    $ git submodule update --init

## RESTbukcs sample

### What's RESTBucks ?
See this article.[How to GET a Cup of Coffee](http://www.infoq.com/articles/webber-rest-workflow)

### Run Sample
    $ php docs/sample/01-rest-bucks/order.php 
	201: Created
	Location: app://self/RestBucks/Order/?id=1234
	
### Client Code
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

### Service Code

Order

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

Payment

	class Payment
	{
		public function onPut($order_id, $credit_card_number, $expires, $name, $amount)
		{
		   // payment transaction here..
		   $this->code = 201;
		   $this->headers['Location'] = "app://self/RestBucks/Order/?id=$order_id";
		   return $this;
		}
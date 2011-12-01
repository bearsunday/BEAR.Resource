<?php

namespace BEAR\Resource;

use Ray\Di\Annotation,
Ray\Di\Config,
Ray\Di\Forge,
Ray\Di\Container,
Ray\Di\Manager,
Ray\Di\Injector,
Ray\Di\EmptyModule,
BEAR\Resource\Builder,
BEAR\Resource\Mock\User;

/**
 * Test class for PHP.Skelton.
 */
class RestBucksTest extends \PHPUnit_Framework_TestCase
{
    protected $skelton;

    protected function setUp()
    {
        parent::setUp();
        $schemeAdapters = array('nop' => '\BEAR\Resource\Adapter\Nop',
                                'prov' => '\BEAR\Resource\Mock\Prov'
        );
        $injector = new Injector(new Container(new Forge(new Config(new Annotation))), new EmptyModule);
        $namespace = array('self' => 'testworld');
        $resourceAdapters = array(
                'app' => new \BEAR\Resource\Adapter\App($injector, $namespace),
                'page' => new \BEAR\Resource\Adapter\Page($injector, $namespace),
                'nop' => new \BEAR\Resource\Adapter\Nop,
                'prov' => new \BEAR\Resource\Adapter\Prov,
                'provc' => function() {
        return new \BEAR\Resource\Adapter\Prov;
        }
        );
        $factory = new Factory($injector, $resourceAdapters);
        $invoker = new Invoker(new Config, new Linker);
        $this->resource = new Client($factory, $invoker, new Request($invoker));
        $this->user = $factory->newInstance('app://self/user');
        $this->nop = $factory->newInstance('nop://self/dummy');
        $this->query = array(
            'id' => 10,
            'name' => 'Ray',
            'age' => 43
        );

    }

    public function test_New()
    {
        $this->assertInstanceOf('\BEAR\Resource\Client', $this->resource);
    }

    public function testOption()
    {
        $options = $this->resource->options->uri('app://self/RestBucks/Menu')->eager->request();
        $allows = $options['allows'];
        asort($options['allows']);
        $expected = array('Get');
        $this->assertSame($expected, $options['allows']);
    }

    public function tesMenuLinksOrder()
    {
        $menu = $this->resource->get->uri('app://self/RestBucks/Menu')->withQuery(array('drink' => 'latte'))->eager->request();
        $orderUri = $menu->headers['rel=order'];
        $response = $this->resource->post->uri($orderUri)->addQuery(array('drink' => $menu['drink']))->eager->request();
        $expected = 201;
        $this->assertSame($expected, $response->code);
        $expected = 'app://self/RestBucks/Order/?id=1234';
        $this->assertSame($expected, $response->headers['Location']);
    }

    public function testOrderLinksPaymentAddQuery()
    {
        $order = array('drink' => 'latte');
        $order = $this->resource->post->uri('app://self/RestBucks/Order')->withQuery($order)->eager->request();
        $paymentUri = $order->headers['rel=payment'];
        $payment = array('credit_card_number' => '123456789', 'expires' => '07/07', 'name' => 'John Citizen', 'amount' => '4.00');
        $response = $this->resource->put->uri($paymentUri)->addQuery($payment)->eager->request();
        $expected = 201;
        $this->assertSame($expected, $response->code);
        $expected = 'app://self/RestBucks/Order/?id=1234';
        $this->assertSame($expected, $response->headers['Location']);
    }

}

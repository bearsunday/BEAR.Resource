<?php

namespace BEAR\Resource;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
use Ray\Aop\Arguments;
use Ray\Aop\ReflectiveMethodInvocation;

class ByProviderTestClass
{
    public function onGet($id, $name = 'koriym')
    {
        return "$id $name";
    }
}

class IdProvider implements ParamProviderInterface
{
    public static $data = [];

    public function __invoke(ParamInterface $param)
    {
        self::$data['method'] = $param->getMethodInvocation();
        self::$data['param'] = $param->getParameter();

        return $param->inject(1002);
    }
}

class IdProviderSkip implements ParamProviderInterface
{
    public function __invoke(ParamInterface $param)
    {
        return;
    }
}

class ByProviderMethodClass
{
    /**
     * @Provider("provider")
     */
    public function onPut($name, $date, $age)
    {
        return "$name, $date, $age";
    }

    public function provider()
    {
        return [
            'name' => 'chill',
            'date' => '0908'
        ];
    }
}

class Provider implements ParamProviderInterface
{
    public function __invoke(ParamInterface $param)
    {
        return $param->inject(1002);
    }
}

class SignalParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SignalParameter
     */
    private $param;

    /**
     * @var Manager
     */
    private $signal;

    public function setUp()
    {
        $this->signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $this->param = new SignalParameter($this->signal, new Param);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Resource\SignalParameter', $this->param);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function testGetArg()
    {
        $callable = [new ByProviderTestClass, 'onGet'];
        $this->param->getArg(new \ReflectionParameter($callable, 'id'), new ReflectiveMethodInvocation($callable[0], new \ReflectionMethod($callable[0], $callable[1]), new Arguments([])));
    }

    public function testGetArgWithSignal()
    {
        $this->param->attachParamProvider('id', new IdProvider);
        $callable = [new ByProviderTestClass, 'onGet'];
        $result = $this->param->getArg(
            new \ReflectionParameter($callable, 'id'),
            new ReflectiveMethodInvocation($callable[0], new \ReflectionMethod($callable[0], $callable[1]), new Arguments([]))
        );
        $this->assertSame(1002, $result);
    }

    /**
     * @depends testGetArgWithSignal
     */
    public function testGetArgWithSignalMethod()
    {
        $this->assertInstanceOf('Ray\Aop\ReflectiveMethodInvocation', IdProvider::$data['method']);
    }

    /**
     * @depends testGetArgWithSignal
     */
    public function testGetArgWithSignalParam()
    {
        $this->assertInstanceOf('ReflectionParameter', IdProvider::$data['param']);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function testGetArgWithSignalFault()
    {
        $this->param->attachParamProvider('invalid_xxx_id', new IdProvider);
        $callable = [new ByProviderTestClass, 'onGet'];
        $result = $this->param->getArg(
            new \ReflectionParameter($callable, 'id'),
            new ReflectiveMethodInvocation($callable[0], new \ReflectionMethod($callable[0], $callable[1]), new Arguments([]))
        );
        $this->assertSame(1002, $result);
    }

    public function testGetArgWithSignalSkip()
    {
        $this->param->attachParamProvider('id', new IdProviderSkip);
        $this->param->attachParamProvider('id', new IdProvider);
        $callable = [new ByProviderTestClass, 'onGet'];
        $result = $this->param->getArg(
            new \ReflectionParameter($callable, 'id'),
            new ReflectiveMethodInvocation($callable[0], new \ReflectionMethod($callable[0], $callable[1]), new Arguments([]))
        );
        $this->assertSame(1002, $result);
    }
}

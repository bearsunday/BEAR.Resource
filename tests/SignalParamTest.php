<?php

namespace BEAR\Resource;

use Aura\Signal\HandlerFactory;
use Aura\Signal\Manager;
use Aura\Signal\ResultCollection;
use Aura\Signal\ResultFactory;
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
    public function __invoke(Param $param)
    {
        return $param->inject(1002);
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
    public function __invoke(Param $param)
    {
        return $param->inject(1002);
    }
}

class SignalParamTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SignalParam
     */
    private $param;

    /**
     * @var Manager
     */
    private $signal;

    public function setUp()
    {
        $this->signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $this->param = new SignalParam($this->signal, new Param);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Resource\SignalParam', $this->param);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\Parameter
     */
    public function testGetArg()
    {
        $callable = [new ByProviderTestClass, 'onGet'];
        $this->param->getArg(new \ReflectionParameter($callable, 'id'), new ReflectiveMethodInvocation($callable, []));
    }

    public function testGetArgWithSignal()
    {
        $this->param->attachParamProvider('id', new IdProvider);
        $callable = [new ByProviderTestClass, 'onGet'];
        $result = $this->param->getArg(
            new \ReflectionParameter($callable, 'id'),
            new ReflectiveMethodInvocation($callable, [])
        );
        $this->assertSame(1002, $result);
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
            new ReflectiveMethodInvocation($callable, [])
        );
        $this->assertSame(1002, $result);
    }
}
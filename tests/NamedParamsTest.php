<?php

namespace BEAR\Resource;

use Ray\Aop\ReflectiveMethodInvocation;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;

class ReflectiveParamsTestClass
{
    public function onGet($id, $name='koriym')
    {
        return "$id $name";
    }
}

class NamedParamsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NamedParams
     */
    private $params;

    public function setUp()
    {
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $this->params = new NamedParams(new SignalParam($signal, new Param));
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\NamedParams', $this->params);
    }

    public function testGetParams()
    {
        $object = new ReflectiveParamsTestClass;
        $namedArgs = ['id' => 1, 'name' => 'koriym'];

        $result = $this->params->invoke(new ReflectiveMethodInvocation([$object, 'onGet'], $namedArgs));
        $this->assertSame("1 koriym", $result);
    }

    public function testGetParamsOrderChanged()
    {
        $object = new ReflectiveParamsTestClass;
        $namedArgs = ['name' => 'koriym', 'id' => 1];

        $result = $this->params->invoke(new ReflectiveMethodInvocation([$object, 'onGet'], $namedArgs));
        $this->assertSame("1 koriym", $result);
    }
}

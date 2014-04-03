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

class NamedParameterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NamedParameter
     */
    private $params;

    public function setUp()
    {
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $this->params = new NamedParameter(new SignalParameter($signal, new Param));
    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Resource\NamedParameter', $this->params);
    }

    public function testGetParams()
    {
        $object = new ReflectiveParamsTestClass;
        $namedArgs = ['id' => 1, 'name' => 'koriym'];

        $args = $this->params->getArgs([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testGetParamsOrderChanged()
    {
        $object = new ReflectiveParamsTestClass;
        $namedArgs = ['name' => 'koriym', 'id' => 1];

        $args = $this->params->getArgs([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }
}

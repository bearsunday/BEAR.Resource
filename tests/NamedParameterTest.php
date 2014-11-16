<?php

namespace BEAR\Resource;

use BEAR\Resource\Exception\ParameterException;

class ReflectiveParamsTestClass
{
    public function onGet($id, $name = 'koriym')
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
        parent::setUp();
        $this->params = new NamedParameter;
    }

    public function testGetParameters()
    {
        $object = new ReflectiveParamsTestClass;
        $namedArgs = ['id' => 1, 'name' => 'koriym'];
        $args = $this->params->getParameters([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testDefaultValue()
    {
        $object = new ReflectiveParamsTestClass;
        $namedArgs = ['id' => 1];
        $args = $this->params->getParameters([$object, 'onGet'], $namedArgs);
        $this->assertSame([1, 'koriym'], $args);
    }

    public function testParameterException()
    {
        $this->setExpectedException(ParameterException::class);
        $object = new ReflectiveParamsTestClass;
        $namedArgs = [];
        $this->params->getParameters([$object, 'onGet'], $namedArgs);
    }
}
